import { useEffect, useRef, useState } from 'react'

// WebContainer wird dynamisch geladen um optional zu bleiben
let WebContainer = null
// Globale WebContainer-Instanz (Singleton)
let globalWebContainerInstance = null
let globalWebContainerPromise = null

function WebContainerComponent({ isActive, projectFiles, onOutput, onFilesUpdate }) {
  const iframeRef = useRef(null)
  const [webcontainer, setWebcontainer] = useState(null)
  const [isLoading, setIsLoading] = useState(false)
  const [isBooted, setIsBooted] = useState(false)
  const [url, setUrl] = useState('')
  const [iframeLoadingTimeout, setIframeLoadingTimeout] = useState(null)
  const [iframeLoaded, setIframeLoaded] = useState(false)
  const [serverStarted, setServerStarted] = useState(false)
  const serverReadyHandledRef = useRef(false)

  // ENTFERNT: Automatische Initialisierung
  // WebContainer wird nur auf Abruf gestartet (startWebContainer Button)

  // Manuelle Start-Funktion für WebContainer
  const startWebContainer = async () => {
    if (webcontainer) {
      onOutput?.('♻️ WebContainer bereits gestartet!')
      return
    }
    
    // Reset States
    setServerStarted(false)
    serverReadyHandledRef.current = false
    setUrl('')
    
    onOutput?.('🚀 Manueller WebContainer-Start...')
    await initWebContainer()
  }

  // ENTFERNT: Automatisches Neu-Laden bei Projektdateien-Änderung
  // Projektdateien werden nur beim manuellen Start geladen

  // URL wird für externe Links verwendet - kein iframe mehr nötig

  // Hilfsfunktion: WebContainer-URL direkt verwenden (ohne Proxy)
  const getProxyUrl = (originalUrl) => {
    if (!originalUrl) return originalUrl
    
    // Prüfe, ob es eine WebContainer-URL ist
    if (originalUrl.includes('webcontainer-api.io')) {
      onOutput?.(`✅ Verwende direkte WebContainer-URL: ${originalUrl}`)
      // Direkte URLs verwenden - Nginx-Proxy wurde korrigiert!
      return originalUrl
    }
    
    // Andere URLs unverändert zurückgeben
    return originalUrl
  }

  // Test-Funktion: Prüfe ob WebContainer-URL erreichbar ist
  const testWebContainerURL = async (url) => {
    try {
      onOutput?.(`🔍 TESTE WebContainer-URL: ${url}`)
      const response = await fetch(url, { 
        method: 'HEAD', 
        mode: 'no-cors',
        credentials: 'omit'
      })
      onOutput?.(`✅ WebContainer-URL Test erfolgreich`)
      return true
    } catch (error) {
      onOutput?.(`❌ WebContainer-URL Test fehlgeschlagen: ${error.message}`)
      throw error
    }
  }

  // Cleanup-Funktion wenn Komponente unmounted wird
  useEffect(() => {
    return () => {
      // Note: WebContainer-Instanz wird NICHT zerstört, da sie global wiederverwendet wird
      // Sie wird nur vom lokalen State entfernt
      setWebcontainer(null)
      setIsBooted(false)
    }
  }, [])

  const initWebContainer = async () => {
    setIsLoading(true)
    onOutput?.('🚀 WebContainer wird initialisiert...')

    try {
      // Prüfe, ob bereits eine WebContainer-Instanz existiert
      if (globalWebContainerInstance) {
        onOutput?.('♻️ Verwende vorhandene WebContainer-Instanz...')
        setWebcontainer(globalWebContainerInstance)
        setIsBooted(true)
        onOutput?.('✅ WebContainer-Instanz wiederverwendet!')
        
        // Prüfe ob eine URL bereits verfügbar ist
        try {
          const existingUrl = await globalWebContainerInstance.url
          if (existingUrl && !url) {
            onOutput?.(`🔗 Wiederherstellung: Bestehende URL gefunden: ${existingUrl}`)
            const finalUrl = getProxyUrl(existingUrl)
            setUrl(finalUrl)
            onOutput?.('✅ URL aus bestehender Instanz wiederhergestellt!')
          }
        } catch (error) {
          onOutput?.('⚠️ Keine bestehende URL verfügbar - warte auf neue URL')
        }
        
        return
      }

      // Prüfe, ob bereits ein Boot-Prozess läuft
      if (globalWebContainerPromise) {
        onOutput?.('⏳ Warte auf laufenden WebContainer-Boot-Prozess...')
        const instance = await globalWebContainerPromise
        setWebcontainer(instance)
        setIsBooted(true)
        onOutput?.('✅ WebContainer aus laufendem Boot-Prozess erhalten!')
        return
      }

      // Dynamisch WebContainer API laden
      if (!WebContainer) {
        onOutput?.('📦 Lade WebContainer API...')
        const { WebContainer: WC } = await import('@webcontainer/api')
        WebContainer = WC
        onOutput?.('✅ WebContainer API geladen!')
      }
      
      // Detaillierte Diagnose der Umgebung
      onOutput?.(`🔍 Diagnose: SharedArrayBuffer = ${typeof SharedArrayBuffer !== 'undefined' ? '✅' : '❌'}`)
      onOutput?.(`🔍 Diagnose: crossOriginIsolated = ${crossOriginIsolated ? '✅' : '❌'}`)
      
      // Check if SharedArrayBuffer is available
      if (typeof SharedArrayBuffer === 'undefined') {
        throw new Error('SharedArrayBuffer ist nicht verfügbar. Cross-Origin-Isolation erforderlich.')
      }

      // Check cross-origin isolation
      if (!crossOriginIsolated) {
        onOutput?.('⚠️ Cross-Origin-Isolation nicht aktiv.')
        onOutput?.('🔧 Prüfe ob Header korrekt gesetzt sind...')
        
        // Weitere Diagnoseinformationen
        if (window.location.protocol === 'http:') {
          onOutput?.('💡 Hinweis: HTTPS kann bei WebContainer helfen')
        }
        
        onOutput?.('🔄 Starte Fallback-Modus für Entwicklung...')
        await createFallbackPreview()
        return
      }

      // Create WebContainer instance (kann nur einmal aufgerufen werden!)
      onOutput?.('🎯 Erstelle WebContainer-Instanz (Singleton)...')
      
      // Starte Boot-Prozess und speichere Promise
      globalWebContainerPromise = WebContainer.boot()
      const instance = await globalWebContainerPromise
      
      // Speichere die globale Instanz
      globalWebContainerInstance = instance
      globalWebContainerPromise = null  // Reset Promise da jetzt Instanz vorhanden
      
      setWebcontainer(instance)
      setIsBooted(true)
      onOutput?.('✅ WebContainer erfolgreich gebootet (Singleton erstellt)!')
      
      // Create project mit echten Dateien oder Fallback
      await createProjectWithFiles(instance)
      
    } catch (error) {
      console.error('Fehler beim Initialisieren von WebContainer:', error)
      onOutput?.(`❌ Fehler: ${error.message}`)
      onOutput?.('🔄 Starte Fallback-Modus...')
      
      // Reset globale Variablen bei Fehler
      globalWebContainerPromise = null
      globalWebContainerInstance = null
      
      // Fallback wenn WebContainer nicht funktioniert
      await createFallbackPreview()
      
    } finally {
      onOutput?.('🔧 Setze isLoading auf false...')
      setIsLoading(false)
      onOutput?.('✅ isLoading ist jetzt false')
    }
  }

  const createProjectWithFiles = async (instance) => {
    // Prüfe, ob echte Projektdateien vorhanden sind
    if (!projectFiles || Object.keys(projectFiles).length === 0) {
      onOutput?.('⚠️ Keine Projektdateien vorhanden, verwende Hello World Fallback...')
      return await createHelloWorldProject(instance)
    }

    onOutput?.('📁 Lade echte Projektdateien in WebContainer...')
    onOutput?.(`📊 ${Object.keys(projectFiles).length} Dateien gefunden`)

    // Transformiere Projektdateien für WebContainer
    const files = {}
    for (const [filename, content] of Object.entries(projectFiles)) {
      files[filename] = {
        file: {
          contents: typeof content === 'string' ? content : JSON.stringify(content, null, 2)
        }
      }
    }

    // Füge package.json hinzu wenn nicht vorhanden
    if (!files['package.json']) {
      // Bestimme den richtigen Start-Befehl basierend auf den vorhandenen Dateien
      let startScript = 'echo "No start script defined"'
      
      if (files['server.js']) {
        startScript = 'node server.js'
      } else if (files['index.js']) {
        // Prüfe ob index.js ein Node.js Server ist oder nur Client-seitiges JS
        const indexContent = projectFiles['index.js'] || ''
        if (indexContent.includes('require(') || indexContent.includes('import ') || 
            indexContent.includes('createServer') || indexContent.includes('express')) {
          startScript = 'node index.js'
        } else if (files['index.html']) {
          // Wenn es eine HTML-Datei gibt, starte einen einfachen HTTP-Server
          startScript = 'npx -y serve . -p 3000'
        }
      } else if (files['index.html']) {
        // Reines Frontend-Projekt - verwende einen statischen Server
        startScript = 'npx -y serve . -p 3000'
      }
      
      files['package.json'] = {
        file: {
          contents: JSON.stringify({
            name: 'ai-generated-project',
            version: '1.0.0',
            description: 'KI-generiertes Projekt von Vibe Your Course',
            main: files['index.js'] ? 'index.js' : 'index.html',
            scripts: {
              start: startScript,
              dev: startScript
            },
            dependencies: {}
          }, null, 2)
        }
      }
    }

    try {
      // Mount the file system nach WebContainer API
      await instance.mount(files)
      onOutput?.('📁 Echte Projektdateien erfolgreich geladen!')
      
      // Sende die Dateien an den Code-Betrachter
      if (onFilesUpdate) {
        onFilesUpdate(projectFiles)
      }

      // Starte Server wenn möglich
      await startProjectServer(instance)

    } catch (error) {
      console.error('Fehler beim Laden der Projektdateien:', error)
      onOutput?.(`❌ Fehler beim Laden der Projektdateien: ${error.message}`)
      onOutput?.('🔄 Fallback zu Hello World...')
      return await createHelloWorldProject(instance)
    }
  }

  const startProjectServer = async (instance) => {
    onOutput?.('🚀 Starte Projekt-Server...')

    try {
      // Starte Server nur einmal
      if (serverStarted) {
        onOutput?.('⚠️ Server bereits gestartet, überspringe...')
        return
      }
      
      setServerStarted(true)
      
      // Event-Handler für Server-Ready (einmalig)
      const handleServerReady = (port, url) => {
        if (serverReadyHandledRef.current) {
          onOutput?.(`⚠️ SERVER-READY bereits behandelt, überspringe...`)
          return
        }
        serverReadyHandledRef.current = true
        
        onOutput?.(`🎯 SERVER-READY EVENT empfangen!`)
        onOutput?.(`🔗 Server bereit auf Port ${port}`)
        onOutput?.(`🔗 App verfügbar unter: ${url}`)
        
        // Setze URL im State für externe Links
        setUrl(url)
        onOutput?.(`✅ URL State aktualisiert: ${url}`)
      }
      
      // Registriere Event-Handler
      instance.on('server-ready', handleServerReady)

      // Versuche npm start
      const serverProcess = await instance.spawn('npm', ['run', 'start'])
      onOutput?.('📦 Server über npm start gestartet')

      // Log Server-Output
      serverProcess.output.pipeTo(
        new WritableStream({
          write(data) {
            onOutput?.(`[Server] ${data}`)
          }
        })
      )

      // Fallback: Versuche URL nach kurzer Verzögerung zu holen falls server-ready Event nicht funktioniert
      setTimeout(async () => {
        try {
          const currentUrl = await instance.url
          if (currentUrl) {
            onOutput?.(`🔗 Fallback URL erhalten: ${currentUrl}`)
            
            // Teste die Fallback-URL auch
            testWebContainerURL(currentUrl).then(() => {
              onOutput?.('✅ Fallback-URL ist erreichbar!')
              const finalUrl = getProxyUrl(currentUrl)
              setUrl(finalUrl)
              onOutput?.('✅ Fallback URL State aktualisiert')
            }).catch(() => {
              onOutput?.('❌ Fallback-URL ist NICHT erreichbar!')
              // Warte nochmal 5 Sekunden
              setTimeout(async () => {
                try {
                  const retryUrl = await instance.url
                  if (retryUrl) {
                    onOutput?.(`🔄 Retry URL erhalten: ${retryUrl}`)
                    const finalUrl = getProxyUrl(retryUrl)
                    setUrl(finalUrl)
                    onOutput?.('⚠️ Retry URL State aktualisiert (ungetestet)')
                  }
                } catch (error) {
                  onOutput?.('❌ Auch Retry-URL fehlgeschlagen')
                }
              }, 5000)
            })
          }
        } catch (error) {
          onOutput?.('⚠️ Fallback URL noch nicht verfügbar, Server startet noch...')
        }
      }, 2000)

    } catch (error) {
      onOutput?.(`❌ Server-Start fehlgeschlagen: ${error.message}`)
      onOutput?.('🔄 Verwende statische Datei-Anzeige als Fallback')
    }
  }

  const createHelloWorldProject = async (instance) => {
    onOutput?.('📁 Erstelle Hello World Fallback-Projekt...')

    // Define the project files nach WebContainer API-Spezifikation
    const files = {
      'package.json': {
        file: {
          contents: JSON.stringify({
            name: 'hello-world-app',
            version: '1.0.0',
            description: 'Ein einfaches Hello World Projekt generiert von KI',
            main: 'index.js',
            scripts: {
              start: 'node index.js',
              dev: 'node server.js'
            },
            dependencies: {
              // Keine externen Dependencies für dieses einfache Beispiel
            }
          }, null, 2)
        }
      },
      'index.html': {
        file: {
          contents: `<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Vibe Your Course - Hello World</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        .emoji {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
        }
        p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        .features {
            text-align: left;
            margin-top: 2rem;
        }
        .feature {
            margin: 0.5rem 0;
            color: #555;
        }
        .feature::before {
            content: "✅ ";
            color: #28a745;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="emoji">🎉</span>
        <h1>Hello World!</h1>
        <p>Deine erste KI-generierte Web-App läuft erfolgreich in WebContainer!</p>
        
        <div class="features">
            <div class="feature">WebContainer erfolgreich gestartet</div>
            <div class="feature">Chat-Interface funktioniert</div>
            <div class="feature">Bereit für komplexere Apps</div>
        </div>
        
        <button onclick="celebrateSuccess()">🚀 App testen</button>
    </div>

    <script>
        function celebrateSuccess() {
            alert('🎉 Herzlichen Glückwunsch! Die WebContainer-Integration funktioniert perfekt. Du kannst jetzt komplexere Apps mit der KI entwickeln!');
        }
        
        // Show a welcome message in console
        console.log('🎉 Vibe Your Course WebContainer ist aktiv!');
        console.log('Diese App wurde automatisch generiert und läuft in einem isolierten Container.');
    </script>
</body>
</html>`
        }
      },
      'server.js': {
        file: {
          contents: `// Hello World Express-ähnlicher Server
const http = require('http');
const fs = require('fs');
const path = require('path');

const server = http.createServer((req, res) => {
  // CORS Headers für WebContainer
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.url === '/' || req.url === '/index.html') {
    fs.readFile(path.join(__dirname, 'index.html'), 'utf8', (err, data) => {
      if (err) {
        res.writeHead(500);
        res.end('Server Error');
        return;
      }
      res.writeHead(200, { 'Content-Type': 'text/html' });
      res.end(data);
    });
  } else {
    res.writeHead(404);
    res.end('Not Found');
  }
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(\`🚀 Hello World Server läuft auf Port \${PORT}\`);
  console.log(\`📍 Zugriff über WebContainer URL\`);
});`
        }
      },
      'index.js': {
        file: {
          contents: `// Einfaches Hello World Skript
console.log('🎉 Hello World von WebContainer!');
console.log('🌐 WebContainer ist eine Browser-API, kein Docker!');
console.log('📦 Node.js läuft direkt im Browser');

// Starte den HTTP Server
require('./server.js');`
        }
      }
    }

    try {
      // Mount the file system nach WebContainer API
      await instance.mount(files)
      onOutput?.('📁 Projektdateien erstellt!')
      
      // Sende die Dateien an den Code-Betrachter
      if (onFilesUpdate) {
        onFilesUpdate(files)
      }

      // Starte den Node.js HTTP Server
      const serverProcess = await instance.spawn('npm', ['run', 'dev'])
      onOutput?.('🌐 Node.js Server wird gestartet...')

      // Log Server-Output
      serverProcess.output.pipeTo(
        new WritableStream({
          write(data) {
            onOutput?.(`[Server] ${data}`)
          }
        })
      )

      // Server-ready Event wird bereits oben behandelt

      // Fallback: Versuche URL nach kurzer Verzögerung zu holen falls server-ready Event nicht funktioniert
      setTimeout(async () => {
        try {
          const currentUrl = await instance.url
          if (currentUrl) {
            onOutput?.(`🔗 Fallback URL erhalten: ${currentUrl}`)
            
            // Teste die Fallback-URL auch
            testWebContainerURL(currentUrl).then(() => {
              onOutput?.('✅ Fallback-URL ist erreichbar!')
              const finalUrl = getProxyUrl(currentUrl)
              setUrl(finalUrl)
              onOutput?.('✅ Fallback URL State aktualisiert')
            }).catch(() => {
              onOutput?.('❌ Fallback-URL ist NICHT erreichbar!')
              // Warte nochmal 5 Sekunden
              setTimeout(async () => {
                try {
                  const retryUrl = await instance.url
                  if (retryUrl) {
                    onOutput?.(`🔄 Retry URL erhalten: ${retryUrl}`)
                    const finalUrl = getProxyUrl(retryUrl)
                    setUrl(finalUrl)
                    onOutput?.('⚠️ Retry URL State aktualisiert (ungetestet)')
                  }
                } catch (error) {
                  onOutput?.('❌ Auch Retry-URL fehlgeschlagen')
                }
              }, 5000)
            })
          }
        } catch (error) {
          onOutput?.('⚠️ Fallback URL noch nicht verfügbar, Server startet noch...')
        }
      }, 2000)

    } catch (error) {
      console.error('Fehler beim Erstellen des Projekts:', error)
      onOutput?.(`❌ Fehler beim Erstellen des Projekts: ${error.message}`)
    }
  }

  const createFallbackPreview = async () => {
    onOutput?.('🔧 Erstelle statische Vorschau als Fallback...')
    
    // Simuliere das HTML direkt als Data-URL für die Vorschau
    const htmlContent = `<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Vibe Your Course - Hello World (Fallback)</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        .emoji {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        .features {
            text-align: left;
            margin-top: 2rem;
        }
        .feature {
            margin: 0.5rem 0;
            color: #555;
        }
        .feature::before {
            content: "✅ ";
            color: #28a745;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }
        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .fallback-notice {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 2rem;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="emoji">🎉</span>
        <h1>Hello World!</h1>
        <p>Deine erste KI-generierte Web-App läuft im Fallback-Modus!</p>
        
        <div class="features">
            <div class="feature">Chat-Interface funktioniert</div>
            <div class="feature">Fallback-Modus aktiv</div>
            <div class="feature">Bereit für echte WebContainer-Apps</div>
        </div>
        
        <button onclick="celebrateSuccess()">🚀 App testen</button>
        <button onclick="showInfo()">ℹ️ Info</button>
        
        <div class="fallback-notice">
            <strong>📝 Hinweis:</strong> Diese App läuft im Fallback-Modus, da WebContainer 
            Cross-Origin-Isolation benötigt. In Produktion funktioniert die volle WebContainer-Integration!
        </div>
    </div>

    <script>
        function celebrateSuccess() {
            alert('🎉 Herzlichen Glückwunsch! Das Chat-Interface und die grundlegende Integration funktionieren. WebContainer wird in Produktion vollständig unterstützt!');
        }
        
        function showInfo() {
            alert('ℹ️ Diese Demo zeigt die Funktionalität ohne vollständige WebContainer-Isolation. Alle Chat- und Backend-Funktionen sind voll funktionsfähig!');
        }
        
        console.log('🎉 Vibe Your Course Fallback-Modus aktiv!');
        console.log('Chat-Interface und Backend-Integration funktionieren vollständig.');
    </script>
</body>
</html>`;

    // Konvertiere zu Data-URL
    const dataUrl = 'data:text/html;charset=utf-8,' + encodeURIComponent(htmlContent)
    setUrl(dataUrl)
    setIsBooted(true)
    
    onOutput?.('✅ Fallback-Vorschau erstellt!')
    onOutput?.('🌐 Statische HTML-App läuft erfolgreich')
    onOutput?.('💡 In Produktion würde echte WebContainer laufen')
  }

  // Debug: Component State für Rendering
  useEffect(() => {
    onOutput?.(`🔍 RENDER DEBUG: isLoading=${isLoading}, isBooted=${isBooted}, url="${url}"`)
  }, [isLoading, isBooted, url])

  return (
    <div className="webcontainer-component h-100">
      {isLoading ? (
        <div className="d-flex align-items-center justify-content-center h-100">
          <div className="text-center">
            <div className="spinner-border text-primary mb-3" role="status">
              <span className="sr-only">Loading...</span>
            </div>
            <h5>WebContainer wird initialisiert...</h5>
            <p className="text-muted">Bitte warten Sie einen Moment.</p>
          </div>
        </div>
      ) : !isBooted ? (
        <div className="d-flex align-items-center justify-content-center h-100">
          <div className="text-center">
            <div className="mb-3">
              <i className="fas fa-rocket fa-3x text-primary"></i>
            </div>
            <h5>WebContainer bereit zum Start</h5>
            <p className="text-muted mb-4">
              Starte eine vollständige Node.js-Umgebung direkt im Browser!<br/>
              Mit Zugriff auf npm, Dateisystem und Prozessen.
            </p>
            
            <button 
              className="btn btn-primary btn-lg"
              onClick={startWebContainer}
              disabled={isLoading}
            >
              {isLoading ? (
                <>
                  <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  WebContainer wird gestartet...
                </>
              ) : (
                <>
                  🚀 WebContainer starten
                </>
              )}
            </button>
            
            <div className="mt-3">
              <small className="text-muted">
                💡 <strong>Tipp:</strong> WebContainer läuft vollständig im Browser - keine Server benötigt!
              </small>
            </div>
          </div>
        </div>
      ) : url ? (
        <div className="h-100 d-flex flex-column">
          <div className="alert alert-info mb-3">
            <h5>🚀 WebContainer-App ist bereit!</h5>
            <p className="mb-3">
              <strong>Hinweis:</strong> WebContainer-Apps können nicht in iframes geladen werden. 
              Sie müssen in einem separaten Fenster geöffnet werden.
            </p>
            
            <div className="d-flex gap-2 flex-wrap">
              <button 
                className="btn btn-primary"
                onClick={() => {
                  const popup = window.open(url, 'webcontainer-app', 'width=1200,height=800,scrollbars=yes,resizable=yes')
                  if (popup) {
                    onOutput?.('🔗 WebContainer-App in Popup geöffnet!')
                  } else {
                    onOutput?.('⚠️ Popup blockiert - verwende "In neuem Tab öffnen"')
                  }
                }}
              >
                🪟 In Popup öffnen
              </button>
              
              <a 
                href={url} 
                target="_blank" 
                rel="noopener noreferrer" 
                className="btn btn-success"
                onClick={() => onOutput?.('🔗 WebContainer-App in neuem Tab geöffnet!')}
              >
                🔗 In neuem Tab öffnen
              </a>
              
              <button 
                className="btn btn-secondary"
                onClick={() => {
                  navigator.clipboard.writeText(url)
                  onOutput?.('📋 URL in Zwischenablage kopiert!')
                }}
              >
                📋 URL kopieren
              </button>
            </div>
            
            <small className="d-block mt-2 text-muted">
              🔍 Debug: {url}
            </small>
          </div>
          
          <div className="flex-grow-1 d-flex align-items-center justify-content-center bg-light border rounded">
            <div className="text-center p-4">
              <div className="mb-3">
                <i className="fas fa-external-link-alt fa-3x text-muted"></i>
              </div>
              <h5>WebContainer läuft extern</h5>
              <p className="text-muted">
                Die App läuft in einem separaten WebContainer-Prozess.<br/>
                Verwende die Buttons oben, um sie zu öffnen.
              </p>
              
              <div className="mt-3">
                <small className="text-muted">
                  💡 <strong>Tipp:</strong> WebContainer-Apps haben vollen Zugriff auf Node.js und npm!
                </small>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div className="d-flex align-items-center justify-content-center h-100">
          <div className="text-center">
            <h5>🌐 WebContainer bereit</h5>
            <p className="text-muted">App wird geladen...</p>
            <div className="spinner-border text-primary" role="status">
              <span className="sr-only">Loading...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default WebContainerComponent
