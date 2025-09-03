import { useState, useEffect } from 'react'

function SimplePreview({ projectFiles, isActive }) {
  const [iframeContent, setIframeContent] = useState('')
  
  useEffect(() => {
    if (!projectFiles || Object.keys(projectFiles).length === 0) return
    
    // Extrahiere HTML, CSS und JS aus den Projektdateien
    let htmlContent = projectFiles['index.html'] || ''
    let cssContent = projectFiles['style.css'] || projectFiles['styles.css'] || ''
    let jsContent = projectFiles['script.js'] || projectFiles['index.js'] || projectFiles['main.js'] || ''
    
    // Falls kein HTML vorhanden, erstelle ein Basis-Template
    if (!htmlContent) {
      htmlContent = `
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Preview</title>
        </head>
        <body>
            <div id="app"></div>
        </body>
        </html>
      `
    }
    
    // Injiziere CSS und JS in das HTML
    const finalHTML = injectAssetsIntoHTML(htmlContent, cssContent, jsContent)
    setIframeContent(finalHTML)
  }, [projectFiles])
  
  const injectAssetsIntoHTML = (html, css, js) => {
    // Parse HTML string
    const parser = new DOMParser()
    const doc = parser.parseFromString(html, 'text/html')
    
    // Füge CSS hinzu (falls nicht bereits vorhanden)
    if (css && !html.includes('<style>')) {
      const styleTag = doc.createElement('style')
      styleTag.textContent = css
      doc.head.appendChild(styleTag)
    }
    
    // Füge JavaScript hinzu (falls nicht bereits vorhanden)
    if (js && !html.includes('<script>')) {
      const scriptTag = doc.createElement('script')
      scriptTag.textContent = js
      // Füge am Ende des body ein für bessere DOM-Verfügbarkeit
      doc.body.appendChild(scriptTag)
    }
    
    // Konvertiere zurück zu String
    return doc.documentElement.outerHTML
  }
  
  const handleIframeError = (e) => {
    console.error('iframe error:', e)
  }
  
  if (!isActive) {
    return (
      <div className="d-flex align-items-center justify-content-center h-100">
        <div className="text-center">
          <h5>Preview deaktiviert</h5>
          <p className="text-muted">Generiere zuerst Code mit der KI</p>
        </div>
      </div>
    )
  }
  
  return (
    <div className="simple-preview h-100">
      <div className="preview-header p-2 bg-light border-bottom">
        <div className="d-flex justify-content-between align-items-center">
          <span className="text-muted">
            <i className="fa fa-eye"></i> Live Preview (Sandboxed iframe)
          </span>
          <button 
            className="btn btn-sm btn-outline-secondary"
            onClick={() => {
              // Öffne in neuem Fenster für besseres Debugging
              const newWindow = window.open('', '_blank')
              newWindow.document.write(iframeContent)
              newWindow.document.close()
            }}
          >
            <i className="fa fa-external-link"></i> In neuem Tab öffnen
          </button>
        </div>
      </div>
      
      <div className="preview-container" style={{ height: 'calc(100% - 50px)' }}>
        {iframeContent ? (
          <iframe
            title="Code Preview"
            srcDoc={iframeContent}
            sandbox="allow-scripts allow-forms allow-modals"
            style={{
              width: '100%',
              height: '100%',
              border: 'none',
              backgroundColor: 'white'
            }}
            onError={handleIframeError}
          />
        ) : (
          <div className="d-flex align-items-center justify-content-center h-100">
            <div className="text-center text-muted">
              <i className="fa fa-code fa-3x mb-3"></i>
              <p>Warte auf generierten Code...</p>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}

export default SimplePreview
