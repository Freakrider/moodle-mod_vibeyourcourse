import { useState, useEffect } from 'react'
import './App.css'
import WebContainerComponent from './WebContainer'
import CodeViewer from './CodeViewer'

function App() {
  const [config, setConfig] = useState(null)
  const [projects, setProjects] = useState([])
  const [currentView, setCurrentView] = useState('projects') // 'projects' or 'ide'
  const [currentProject, setCurrentProject] = useState(null)
  const [showModal, setShowModal] = useState(false)
  const [isCreatingProject, setIsCreatingProject] = useState(false)
  const [newProject, setNewProject] = useState({
    name: '',
    runtime: 'javascript',
    template: ''
  })
  const [chatMessages, setChatMessages] = useState([])
  const [currentPrompt, setCurrentPrompt] = useState('')
  const [isProcessingPrompt, setIsProcessingPrompt] = useState(false)
  const [activeTab, setActiveTab] = useState('preview')
  const [webcontainerOutput, setWebcontainerOutput] = useState([])
  const [isWebcontainerActive, setIsWebcontainerActive] = useState(false)
  const [projectFiles, setProjectFiles] = useState({})
  const [currentFile, setCurrentFile] = useState('')
  const [courseFiles, setCourseFiles] = useState([])

  useEffect(() => {
    // Get configuration from window object (set by PHP)
    console.log('Looking for window.vibeyourcourseConfig...')
    console.log('window.vibeyourcourseConfig:', window.vibeyourcourseConfig)
    
    if (window.vibeyourcourseConfig) {
      console.log('Configuration found:', window.vibeyourcourseConfig)
      setConfig(window.vibeyourcourseConfig)
      // Load existing projects from configuration initially
      setProjects(window.vibeyourcourseConfig.projects || [])
    } else {
      console.error('window.vibeyourcourseConfig not found!')
      // Retry after a short delay in case the script hasn't loaded yet
      setTimeout(() => {
        if (window.vibeyourcourseConfig) {
          console.log('Configuration found on retry:', window.vibeyourcourseConfig)
          setConfig(window.vibeyourcourseConfig)
          setProjects(window.vibeyourcourseConfig.projects || [])
        }
      }, 100)
    }
  }, [])

  // Load fresh projects when config is available
  useEffect(() => {
    if (config) {
      loadProjects()
      loadCourseFiles()
    }
  }, [config])

  const loadProjects = async () => {
    if (!config) return

    try {
      const response = await fetch('/mod/vibeyourcourse/ajax.php?' + new URLSearchParams({
        action: 'get_projects',
        cmid: config.cmid
      }))

      const result = await response.json()
      
      if (result.success) {
        setProjects(result.projects || [])
      } else {
        console.error('Fehler beim Laden der Projekte:', result.error)
      }
    } catch (error) {
      console.error('Netzwerkfehler beim Laden der Projekte:', error)
    }
  }

  const fetchProjectFiles = async (projectId) => {
    if (!config || !projectId) return

    try {
      console.log('Lade Projektdateien für Projekt ID:', projectId)
      
      const response = await fetch('/mod/vibeyourcourse/ajax.php?' + new URLSearchParams({
        action: 'get_project',
        cmid: config.cmid,
        project_id: projectId
      }))

      const result = await response.json()
      
      if (result.success && result.project.project_files) {
        console.log('Projektdateien geladen:', result.project.project_files)
        setProjectFiles(result.project.project_files)
        
        // Automatisch erste Datei auswählen
        const firstFileName = Object.keys(result.project.project_files)[0]
        if (firstFileName) {
          setCurrentFile(firstFileName)
        }
      } else {
        console.error('Fehler beim Laden der Projektdateien:', result.error)
        setProjectFiles({ 'error.txt': 'Projektdateien konnten nicht geladen werden.' })
      }
    } catch (error) {
      console.error('Netzwerkfehler beim Laden der Projektdateien:', error)
      setProjectFiles({ 'error.txt': 'Netzwerkfehler beim Laden der Dateien.' })
    }
  }

  const loadCourseFiles = async () => {
    if (!config) return

    try {
      const response = await fetch('/mod/vibeyourcourse/ajax.php?' + new URLSearchParams({
        action: 'get_course_files',
        cmid: config.cmid
      }))

      const result = await response.json()
      
      if (result.success) {
        setCourseFiles(result.files || [])
      } else {
        console.error('Fehler beim Laden der Course Files:', result.error)
        setCourseFiles([])
      }
    } catch (error) {
      console.error('Netzwerkfehler beim Laden der Course Files:', error)
      setCourseFiles([])
    }
  }

  const createNewProject = () => {
    setShowModal(true)
  }

  const handleCreateProject = async () => {
    if (!newProject.name.trim()) {
      alert('Bitte gib einen Projektnamen ein!')
      return
    }

    setIsCreatingProject(true)

    try {
      console.log('Erstelle Projekt mit Daten:', {
        action: 'create_project',
        cmid: config.cmid,
        name: newProject.name,
        runtime: newProject.runtime,
        template: newProject.template
      })

      const response = await fetch('/mod/vibeyourcourse/ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'create_project',
          cmid: config.cmid,
          sesskey: config.sesskey,
          name: newProject.name,
          runtime: newProject.runtime,
          template: newProject.template
        })
      })

      const result = await response.json()
      console.log('Backend Response:', result)
      
      if (result.success) {
        console.log('Projekt erfolgreich erstellt:', result.project)
        
        // Add new project to the list - handle different response formats
        const newProjectData = result.project || result.data || {
          id: Date.now(), // Fallback ID
          project_name: newProject.name,
          runtime_type: newProject.runtime,
          status: 'active',
          completion_percentage: 0
        }
        
        setProjects([newProjectData, ...(projects || [])])
        setShowModal(false)
        setNewProject({ name: '', runtime: 'javascript', template: '' })
        
        // Reload projects to get fresh data from server
        setTimeout(() => {
          loadProjects()
        }, 500)
      } else {
        console.error('Backend Error:', result.error)
        alert('Fehler beim Erstellen des Projekts: ' + (result.error || 'Unbekannter Fehler'))
      }
    } catch (error) {
      console.error('Fehler beim Erstellen des Projekts:', error)
      alert('Netzwerkfehler beim Erstellen des Projekts')
    } finally {
      setIsCreatingProject(false)
    }
  }

  const openProject = (project) => {
    console.log('Öffne Projekt:', project)
    
    // State für neues Projekt zurücksetzen
    setCurrentProject(project)
    setProjectFiles({})  // Alte Dateien löschen
    setCurrentFile('')   // Aktuelle Datei zurücksetzen
    setChatMessages([])  // Chat-Verlauf löschen
    setWebcontainerOutput([]) // Console löschen
    
    // Projektdateien vom Server laden
    fetchProjectFiles(project.id)
    
    // Zur IDE wechseln
    setCurrentView('ide')
    
    // WebContainer beim Öffnen der IDE vorbereiten
    setIsWebcontainerActive(true)
  }

  const closeIDE = () => {
    setCurrentView('projects')
    setCurrentProject(null)
  }

  const saveProject = () => {
    // TODO: Implement save functionality
    console.log('Saving project...')
  }

  const runCode = () => {
    // TODO: Implement code execution
    console.log('Running code...')
  }

  const sendPrompt = async () => {
    if (!currentPrompt.trim()) return

    const userMessage = {
      id: Date.now(),
      type: 'user',
      content: currentPrompt,
      timestamp: new Date()
    }

    setChatMessages(prev => [...prev, userMessage])
    setCurrentPrompt('')
    setIsProcessingPrompt(true)

    try {
      // TODO: Claude-API Integration hier später implementieren
      // Erstmal zeigen wir nur eine Dummy-Antwort und starten WebContainer

      const response = await fetch('/mod/vibeyourcourse/ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'process_prompt',
          cmid: config.cmid,
          sesskey: config.sesskey,
          prompt: currentPrompt,
          project_id: currentProject?.id || 0
        })
      })

      const result = await response.json()
      console.log('Backend Response:', result)
      if (result.success) {
        const aiMessage = {
          id: Date.now() + 1,
          type: 'ai',
          content: result.response || 'Verstanden! Ich starte die WebContainer-Integration für dein Projekt.',
          timestamp: new Date()
        }

        console.log('AI Message:', aiMessage)

        setChatMessages(prev => [...prev, aiMessage])
        
        // WICHTIG: Neue/geänderte Dateien sofort in projectFiles aktualisieren
        if (result.files && Object.keys(result.files).length > 0) {
          console.log('Aktualisiere Projektdateien mit AI-generierten Files:', result.files)
          setProjectFiles(result.files)
          
          // Automatisch zum Preview-Tab wechseln um die Änderungen zu sehen
          setActiveTab('preview')
        }
        
        // WebContainer starten
        startWebContainer()
      } else {
        const errorContent = result.error || 'Entschuldigung, es gab einen Fehler bei der Verarbeitung deines Prompts.'
        const errorMessage = {
          id: Date.now() + 1,
          type: 'ai',
          content: errorContent,
          timestamp: new Date()
        }
        setChatMessages(prev => [...prev, errorMessage])
        
        // Debug-Info in Console loggen falls vorhanden
        if (result.debug_info) {
          console.error('Backend Debug Info:', result.debug_info)
        }
      }
    } catch (error) {
      console.error('Fehler beim Senden des Prompts:', error)
      const errorMessage = {
        id: Date.now() + 1,
        type: 'ai',
        content: 'Netzwerkfehler beim Verarbeiten des Prompts.',
        timestamp: new Date()
      }
      setChatMessages(prev => [...prev, errorMessage])
    } finally {
      setIsProcessingPrompt(false)
    }
  }

  const startWebContainer = () => {
    console.log('Starting WebContainer for Hello World...')
    
    setIsWebcontainerActive(true)
    
    const webcontainerMessage = {
      id: Date.now() + 2,
      type: 'system',
      content: 'WebContainer wird gestartet! Wechsle zum WebContainer-Tab um die App zu sehen.',
      timestamp: new Date()
    }
    
    setChatMessages(prev => [...prev, webcontainerMessage])
    setActiveTab('preview')
  }

  const loadDemoCodeForViewer = () => {
    // Demo-Funktion zum Testen des Code-Betrachters
    const demoFiles = {
      'index.html': {
        file: {
          contents: `<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Demo App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Hello from Code Viewer! 🎉</h1>
        <p>Dies ist eine Demo-Datei für den Code-Betrachter.</p>
        <button onclick="showMessage()">Klick mich!</button>
    </div>
    <script src="script.js"></script>
</body>
</html>`
        }
      },
      'styles.css': {
        file: {
          contents: `/* Demo CSS für Code-Betrachter */
.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    font-family: 'Segoe UI', Arial, sans-serif;
    text-align: center;
}

h1 {
    color: #007bff;
    margin-bottom: 1rem;
    font-size: 2.5rem;
}

p {
    color: #666;
    font-size: 1.2rem;
    margin-bottom: 2rem;
}

button {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background: #218838;
}`
        }
      },
      'script.js': {
        file: {
          contents: `// Demo JavaScript für Code-Betrachter
function showMessage() {
    const messages = [
        "🎉 Der Code-Betrachter funktioniert!",
        "👁️ Du kannst jetzt den generierten Code einsehen!",
        "🚀 Bereit für echte KI-generierte Apps!",
        "💻 Vibe Your Course ist startklar!"
    ];
    
    const randomMessage = messages[Math.floor(Math.random() * messages.length)];
    alert(randomMessage);
}

// Automatically greet when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Demo-Code erfolgreich geladen!');
    console.log('👁️ Code-Betrachter Integration funktioniert!');
});`
        }
      },
      'package.json': {
        file: {
          contents: `{
  "name": "demo-app",
  "version": "1.0.0",
  "description": "Demo-App für den Code-Betrachter",
  "main": "index.html",
  "scripts": {
    "start": "serve .",
    "dev": "serve . --port 3000"
  },
  "keywords": ["demo", "code-viewer", "vibe-your-course"],
  "author": "KI Assistant",
  "license": "MIT"
}`
        }
      },
      'README.md': {
        file: {
          contents: `# 🚀 Demo App für Code-Betrachter

Dies ist eine Demo-Anwendung, um den Code-Betrachter im **Vibe Your Course** Plugin zu testen.

## 📁 Dateien

- \`index.html\` - Hauptseite der App
- \`styles.css\` - Styling für die App  
- \`script.js\` - JavaScript-Funktionalität
- \`package.json\` - NPM-Konfiguration
- \`README.md\` - Diese Dokumentation

## ✨ Features

- ✅ Syntax-Highlighting für verschiedene Sprachen
- ✅ Datei-Explorer mit Icons
- ✅ Zeilennummern
- ✅ Einfache Navigation zwischen Dateien

## 🎯 Verwendung

Der Code-Betrachter zeigt automatisch alle generierten Dateien an und ermöglicht es, durch sie zu navigieren, ohne dass CodeMirror benötigt wird.

**Happy Coding! 🎉**`
        }
      }
    }
    
    handleProjectFilesUpdate(demoFiles)
  }

  const handleWebcontainerOutput = (message) => {
    setWebcontainerOutput(prev => [...prev, {
      timestamp: new Date(),
      message: message
    }])
  }

  const handleProjectFilesUpdate = (files) => {
    // Funktion zum Aktualisieren der Projektdateien für den Code-Betrachter
    setProjectFiles(files)
    
    // Automatisch zum Code-Tab wechseln wenn neue Dateien generiert wurden
    if (files && Object.keys(files).length > 0) {
      setActiveTab('code')
      
      // Wähle die erste Datei automatisch aus
      const firstFile = Object.keys(files)[0]
      setCurrentFile(firstFile)
      
      // Zeige Nachricht im Chat
      const message = {
        id: Date.now() + 10,
        type: 'system',
        content: `📁 ${Object.keys(files).length} Dateien generiert! Schaue dir den Code im Code-Tab an.`,
        timestamp: new Date()
      }
      setChatMessages(prev => [...prev, message])
    }
  }

  if (!config) {
    return (
      <div className="vibeyourcourse-container">
        <div className="text-center p-4">
          <h4>Loading Vibe Your Course...</h4>
          <p>Waiting for configuration...</p>
          {!window.vibeyourcourseConfig && (
            <p style={{color: 'red'}}>
              Error: Configuration not found. Please refresh the page.
            </p>
          )}
        </div>
      </div>
    )
  }

  return (
    <div className="vibeyourcourse-container">
      {currentView === 'projects' ? (
        <>
          {/* Header Section */}
          <div className="vibeyourcourse-header">
            <div className="row align-items-center">
              <div className="col-md-2">
                <div className="vibeyourcourse-brand">
                  <img 
                    src="/mod/vibeyourcourse/pix/monologo.png" 
                    alt="VibeCoding Logo" 
                    className="vibeyourcourse-logo"
                  />
                </div>
              </div>
              <div className="col-md-4">
                <h3>My Projects</h3>
              </div>
              <div className="col-md-6 text-right">
                <button 
                  className="btn btn-primary" 
                  onClick={createNewProject}
                >
                  <i className="fa fa-plus"></i> New Project
                </button>
              </div>
            </div>
      </div>

          {/* Projects Overview */}
          <div className="vibeyourcourse-projects">
            {(!projects || projects.length === 0) ? (
              <div className="empty-state text-center p-4">
                <i className="fa fa-code fa-3x text-muted mb-3"></i>
                <h4>Coding</h4>
                <p className="text-muted">Get started with your first coding project!</p>
                <button 
                  className="btn btn-primary btn-lg" 
                  onClick={createNewProject}
                >
                  New Project
        </button>
              </div>
            ) : (
              <div className="row">
                {(projects || []).filter(project => project && project.id).map((project) => (
                  <div key={project.id} className="col-md-4 mb-3">
                    <div className="card project-card">
                      <div className="card-header">
                        <h5 className="card-title mb-0">{project.project_name || 'Unnamed Project'}</h5>
                        <small className="text-muted">{project.runtime_type || 'Unknown'}</small>
                      </div>
                      <div className="card-body">
                        <div className="progress mb-2">
                          <div 
                            className="progress-bar" 
                            style={{ width: `${project.completion_percentage || 0}%` }}
                          ></div>
                        </div>
                        <small className="text-muted">
                          {project.completion_percentage || 0}% Progress
                        </small>
                        <p className="card-text mt-2">
                          <span className={`badge badge-${project.status === 'completed' ? 'success' : 'secondary'}`}>
                            {project.status || 'Unknown'}
                          </span>
        </p>
      </div>
                      <div className="card-footer">
                        <button 
                          className="btn btn-sm btn-outline-primary"
                          onClick={() => openProject(project)}
                        >
                          Code Editor
                        </button>
                        {project.status === 'completed' && (
                          <button className="btn btn-sm btn-success ml-1">
                            Preview
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </>
      ) : (
        /* IDE Container */
        <div className="vibeyourcourse-ide">
          <div className="ide-header">
            <div className="row align-items-center">
              <div className="col-md-2">
                <div className="vibeyourcourse-brand">
                  <img 
                    src="/mod/vibeyourcourse/pix/monologo.png" 
                    alt="VibeCoding Logo" 
                    className="vibeyourcourse-logo vibeyourcourse-logo-small"
                  />
                </div>
              </div>
              <div className="col-md-4">
                <h4>Project Name</h4>
              </div>
              <div className="col-md-6 text-right">
                <button 
                  className="btn btn-sm btn-outline-secondary"
                  onClick={saveProject}
                >
                  Save Project
                </button>
                <button 
                  className="btn btn-sm btn-primary ml-1"
                  onClick={runCode}
                >
                  Run Code
                </button>
                <button 
                  className="btn btn-sm btn-secondary ml-1"
                  onClick={closeIDE}
                >
                  Back to Course
                </button>
              </div>
            </div>
          </div>

          <div className="ide-body">
            <div className="row h-100">
              {/* Left Sidebar - Files & Chat */}
              <div className="col-md-3 ide-left-sidebar d-flex flex-column" style={{ borderRight: '1px solid #ddd' }}>
                              {/* Course Files */}
                <div className="files-section mb-3">
                  <h6 className="p-2 bg-light mb-0 border-bottom">📁 Course Files</h6>
                  <div className="file-explorer-content p-2" style={{ maxHeight: '200px', overflow: 'auto' }}>
                    {courseFiles.length === 0 ? (
                      <div className="text-center text-muted">
                        <small>Keine Course Files</small>
                      </div>
                    ) : (
                      courseFiles.map((file, index) => (
                        <div 
                          key={index}
                          className="file-item p-1 mb-1 rounded cursor-pointer bg-light"
                          onClick={() => window.open(file.url, '_blank')}
                          style={{ cursor: 'pointer', fontSize: '0.85rem' }}
                          title={`${file.name} (${file.size})`}
                        >
                          <div className="d-flex align-items-center">
                            <span className="file-icon mr-1" style={{ fontSize: '0.8rem' }}>
                              {file.type === 'pdf' ? '📄' :
                               file.type === 'powerpoint' ? '📊' :
                               file.type === 'word' ? '📝' :
                               file.type === 'excel' ? '📈' :
                               file.type === 'archive' ? '📦' :
                               file.type === 'image' ? '🖼️' :
                               file.type === 'video' ? '🎥' :
                               file.type === 'audio' ? '🎵' :
                               file.type === 'text' ? '📄' : 
                               file.type === 'file' ? '📁' : '📄'}
                            </span>
                            <div className="file-details flex-grow-1">
                              <span className="file-name d-block" title={file.name}>
                                {file.name.length > 12 ? `${file.name.substring(0, 9)}...` : file.name}
                              </span>
                              <small className="text-muted">{file.size}</small>
                            </div>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>

                {/* Chat Section */}
                <div className="chat-section flex-grow-1 d-flex flex-column">
                  <h6 className="p-2 bg-light mb-0 border-bottom">💬 KI Chat</h6>
                  <div className="chat-messages flex-grow-1 p-2" style={{ overflow: 'auto', fontSize: '0.85rem' }}>
                    {chatMessages.length === 0 ? (
                      <div className="text-center text-muted mt-3">
                        <p style={{ fontSize: '0.8rem' }}>💡 App-Idee beschreiben!</p>
                        <small style={{ fontSize: '0.7rem' }}>Beispiel: "Todo-App mit React"</small>
                        <div className="mt-2">
                          <button 
                            className="btn btn-sm btn-outline-primary"
                            onClick={loadDemoCodeForViewer}
                            style={{ fontSize: '0.7rem' }}
                          >
                            👁️ Demo Code
                          </button>
                        </div>
                      </div>
                    ) : (
                      chatMessages.map((message) => (
                        <div key={message.id} className={`message mb-2 ${message.type}`}>
                          <div className={`badge ${
                            message.type === 'user' ? 'badge-primary' : 
                            message.type === 'ai' ? 'badge-success' : 'badge-info'
                          } mb-1`} style={{ fontSize: '0.65rem' }}>
                            {message.type === 'user' ? '👤' : 
                             message.type === 'ai' ? '🤖' : '⚙️'}
                          </div>
                          <div className="message-content p-2 bg-light rounded" style={{ fontSize: '0.75rem' }}>
                            {message.content}
                          </div>
                          <small className="text-muted" style={{ fontSize: '0.65rem' }}>
                            {message.timestamp.toLocaleTimeString()}
                          </small>
                        </div>
                      ))
                    )}
                    {isProcessingPrompt && (
                      <div className="message mb-2">
                        <div className="badge badge-success mb-1" style={{ fontSize: '0.65rem' }}>🤖</div>
                        <div className="message-content p-2 bg-light rounded" style={{ fontSize: '0.75rem' }}>
                          <span className="spinner-border spinner-border-sm mr-2"></span>
                          Verarbeite...
                        </div>
                      </div>
                    )}
                  </div>
                  <div className="chat-input p-2 border-top">
                    <div className="input-group input-group-sm">
                      <input
                        type="text"
                        className="form-control"
                        placeholder="App-Idee..."
                        value={currentPrompt}
                        onChange={(e) => setCurrentPrompt(e.target.value)}
                        onKeyPress={(e) => e.key === 'Enter' && sendPrompt()}
                        disabled={isProcessingPrompt}
                        style={{ fontSize: '0.8rem' }}
                      />
                      <div className="input-group-append">
                        <button
                          className="btn btn-primary"
                          onClick={sendPrompt}
                          disabled={isProcessingPrompt || !currentPrompt.trim()}
                          style={{ fontSize: '0.8rem' }}
                        >
                          {isProcessingPrompt ? '⏳' : '🚀'}
                        </button>
                      </div>
                </div>
              </div>
                </div>
              </div>

              {/* Right Section - Code & Preview */}
              <div className="col-md-9 ide-main-content">
                <ul className="nav nav-tabs">
                  <li className="nav-item">
                    <a 
                      className={`nav-link ${activeTab === 'preview' ? 'active' : ''}`}
                      onClick={() => setActiveTab('preview')}
                      style={{ cursor: 'pointer' }}
                    >
                      🌐 Live Preview
                    </a>
                  </li>
                  <li className="nav-item">
                    <a 
                      className={`nav-link ${activeTab === 'code' ? 'active' : ''}`}
                      onClick={() => setActiveTab('code')}
                      style={{ cursor: 'pointer' }}
                    >
                      👁️ Generated Code
                    </a>
                  </li>
                  <li className="nav-item">
                    <a 
                      className={`nav-link ${activeTab === 'console' ? 'active' : ''}`}
                      onClick={() => setActiveTab('console')}
                      style={{ cursor: 'pointer' }}
                    >
                      📺 Console
                    </a>
                  </li>
                </ul>
                <div className="tab-content" style={{ height: 'calc(100vh - 200px)', border: '1px solid #ddd', borderTop: 'none' }}>
                  {/* Code Viewer Tab */}
                  {activeTab === 'code' && (
                    <div className="tab-pane fade show active h-100">
                      <CodeViewer 
                        projectFiles={projectFiles}
                        currentFile={currentFile}
                        onFileSelect={setCurrentFile}
                      />
                    </div>
                  )}
                  
                  {/* WebContainer Preview Tab */}
                  {activeTab === 'preview' && (
                    <div className="tab-pane fade show active h-100">
                      <WebContainerComponent 
                        key={currentProject?.id} // Re-mount bei Projektwechsel
                        isActive={isWebcontainerActive}
                        projectFiles={projectFiles} // Echte Projektdateien übergeben
                        onOutput={handleWebcontainerOutput}
                        onFilesUpdate={handleProjectFilesUpdate}
                      />
                    </div>
                  )}
                  
                  {/* Console Tab */}
                  {activeTab === 'console' && (
                    <div className="tab-pane fade show active h-100">
                      <div className="console h-100 bg-dark text-light p-2" style={{ fontFamily: 'monospace', overflow: 'auto' }}>
                        <div className="text-info">🖥️ WebContainer Console</div>
                        <div className="text-success">$ Waiting for commands...</div>
                        <div className="text-muted">---</div>
                        {webcontainerOutput.map((output, index) => (
                          <div key={index} className="mt-1">
                            <span className="text-muted">{output.timestamp.toLocaleTimeString()}</span>
                            <span className="ml-2">{output.message}</span>
                          </div>
                        ))}
                        {webcontainerOutput.length === 0 && (
                          <div className="text-muted mt-2">
                            Starte einen Prompt, um WebContainer-Output zu sehen...
                          </div>
                        )}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* New Project Modal */}
      {showModal && (
        <div className="modal show" style={{ display: 'block' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">New Project</h5>
                <button 
                  type="button" 
                  className="close" 
                  onClick={() => setShowModal(false)}
                >
                  &times;
                </button>
              </div>
              <div className="modal-body">
                <div className="form-group">
                  <label>Project Name</label>
                  <input 
                    type="text" 
                    className="form-control" 
                    value={newProject.name}
                    onChange={(e) => setNewProject({...newProject, name: e.target.value})}
                    placeholder="Enter a name for your project"
                    required 
                  />
                </div>
                <div className="form-group">
                  <label>Select Runtime</label>
                  <select 
                    className="form-control" 
                    value={newProject.runtime}
                    onChange={(e) => setNewProject({...newProject, runtime: e.target.value})}
                    required
                  >
                    {config?.allowedRuntimes?.filter(runtime => runtime !== 'python').map((runtime) => (
                      <option key={runtime} value={runtime}>
                        {runtime === 'javascript' ? 'JavaScript/Web (WebContainer)' : runtime}
                      </option>
                    )) || [
                      <option key="javascript" value="javascript">JavaScript/Web (WebContainer)</option>
                    ]}
                  </select>
                </div>
                <div className="form-group">
                  <label>Select Template</label>
                  <select 
                    className="form-control"
                    value={newProject.template}
                    onChange={(e) => setNewProject({...newProject, template: e.target.value})}
                  >
                    <option value="">Blank Project</option>
                    {/* Templates will be loaded via JavaScript */}
                  </select>
                </div>
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => setShowModal(false)}
                >
                  Cancel
                </button>
                <button 
                  type="button" 
                  className="btn btn-primary" 
                  onClick={handleCreateProject}
                  disabled={isCreatingProject}
                >
                  {isCreatingProject ? (
                    <>
                      <span className="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                      Creating...
                    </>
                  ) : (
                    'Create Project'
                  )}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default App
