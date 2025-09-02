import { useState, useEffect } from 'react'

function CodeViewer({ projectFiles, currentFile, onFileSelect }) {
  const [selectedFileContent, setSelectedFileContent] = useState('')
  const [selectedFileName, setSelectedFileName] = useState('')
  const [fileList, setFileList] = useState([])

  useEffect(() => {
    // Aktualisiere die Dateiliste basierend auf projectFiles
    if (projectFiles && typeof projectFiles === 'object') {
      const files = Object.keys(projectFiles).map(filename => ({
        name: filename,
        content: projectFiles[filename]?.file?.contents || projectFiles[filename] || ''
      }))
      setFileList(files)
      
      // Wähle die erste Datei automatisch aus, wenn keine ausgewählt ist
      if (files.length > 0 && !currentFile && onFileSelect) {
        onFileSelect(files[0].name)
      }
    }
  }, [projectFiles, currentFile, onFileSelect])

  useEffect(() => {
    // Aktualisiere den Inhalt wenn eine andere Datei ausgewählt wird
    if (currentFile && projectFiles) {
      const fileContent = projectFiles[currentFile]?.file?.contents || projectFiles[currentFile] || ''
      setSelectedFileContent(fileContent)
      setSelectedFileName(currentFile)
    }
  }, [currentFile, projectFiles])

  const selectFile = (filename, content) => {
    setSelectedFileName(filename)
    setSelectedFileContent(content)
    if (onFileSelect) {
      onFileSelect(filename)
    }
  }

  const getFileExtension = (filename) => {
    return filename.split('.').pop().toLowerCase()
  }

  const getLanguageFromExtension = (ext) => {
    const languageMap = {
      'js': 'javascript',
      'jsx': 'javascript',
      'ts': 'typescript',
      'tsx': 'typescript',
      'html': 'html',
      'css': 'css',
      'json': 'json',
      'md': 'markdown',
      'py': 'python',
      'php': 'php',
      'xml': 'xml',
      'yml': 'yaml',
      'yaml': 'yaml'
    }
    return languageMap[ext] || 'text'
  }

  const escapeHtml = (text) => {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }
    return text.replace(/[&<>"']/g, (m) => map[m])
  }

  const formatCode = (content, language) => {
    // Einfache Syntax-Highlighting ohne externe Bibliotheken
    // Nutzt HTML und CSS für grundlegende Hervorhebung
    
    if (!content) return ''
    
    const lines = content.split('\n')
    
    return lines.map((line, index) => {
      // Erst HTML-Escaping, dann Syntax-Highlighting
      let formattedLine = escapeHtml(line)
      
      // JavaScript/TypeScript highlighting
      if (language === 'javascript' || language === 'typescript') {
        // Keywords
        formattedLine = formattedLine.replace(
          /\b(const|let|var|function|return|if|else|for|while|class|extends|import|export|from|async|await)\b/g,
          '<span class="code-keyword">$1</span>'
        )
        // Strings
        formattedLine = formattedLine.replace(
          /(&quot;)((?:[^&]|&(?!quot;))*?)(&quot;)/g,
          '<span class="code-string">$1$2$3</span>'
        )
        formattedLine = formattedLine.replace(
          /(&#039;)((?:[^&]|&(?!#039;))*?)(&#039;)/g,
          '<span class="code-string">$1$2$3</span>'
        )
        // Comments
        formattedLine = formattedLine.replace(
          /(\/\/.*$)/g,
          '<span class="code-comment">$1</span>'
        )
      }
      
      // HTML highlighting
      if (language === 'html') {
        // Tags
        formattedLine = formattedLine.replace(
          /(&lt;\/?[^&]*?&gt;)/g,
          '<span class="code-tag">$1</span>'
        )
        // Attributes
        formattedLine = formattedLine.replace(
          /(\w+)=(&quot;[^&]*?&quot;)/g,
          '<span class="code-attribute">$1</span>=<span class="code-string">$2</span>'
        )
      }
      
      // CSS highlighting
      if (language === 'css') {
        // Selectors
        formattedLine = formattedLine.replace(
          /^([.#]?[\w-]+(?:\s*[,:]\s*[.#]?[\w-]+)*)\s*\{/,
          '<span class="code-selector">$1</span> {'
        )
        // Properties
        formattedLine = formattedLine.replace(
          /(\w+):\s*([^;]+);/g,
          '<span class="code-property">$1</span>: <span class="code-value">$2</span>;'
        )
      }
      
      // JSON highlighting
      if (language === 'json') {
        // Keys
        formattedLine = formattedLine.replace(
          /(&quot;)([^&]+?)(&quot;):/g,
          '<span class="code-json-key">$1$2$3</span>:'
        )
        // String values
        formattedLine = formattedLine.replace(
          /:\s*(&quot;[^&]*?&quot;)/g,
          ': <span class="code-string">$1</span>'
        )
        // Numbers and booleans
        formattedLine = formattedLine.replace(
          /:\s*(\d+|true|false|null)/g,
          ': <span class="code-value">$1</span>'
        )
      }
      
      return {
        number: index + 1,
        content: formattedLine || '&nbsp;'
      }
    })
  }

  const language = selectedFileName ? getLanguageFromExtension(getFileExtension(selectedFileName)) : 'text'
  const formattedLines = formatCode(selectedFileContent, language)

  return (
    <div className="code-viewer h-100 d-flex">
      {/* File Explorer Sidebar */}
      <div className="code-viewer-sidebar" style={{ width: '200px', borderRight: '1px solid #ddd', backgroundColor: '#f8f9fa' }}>
        <div className="p-2 border-bottom bg-light">
          <h6 className="mb-0">📁 Projektdateien</h6>
        </div>
        <div className="file-list p-2">
          {fileList.length === 0 ? (
            <div className="text-muted text-center">
              <small>Keine Dateien generiert</small>
            </div>
          ) : (
            fileList.map((file, index) => (
              <div 
                key={index}
                className={`file-item p-2 mb-1 rounded cursor-pointer ${
                  selectedFileName === file.name ? 'bg-primary text-white' : 'bg-light'
                }`}
                onClick={() => selectFile(file.name, file.content)}
                style={{ cursor: 'pointer', fontSize: '0.9rem' }}
              >
                <div className="d-flex align-items-center">
                  <span className="file-icon mr-2">
                    {getFileExtension(file.name) === 'html' ? '🌐' :
                     getFileExtension(file.name) === 'css' ? '🎨' :
                     getFileExtension(file.name) === 'js' || getFileExtension(file.name) === 'jsx' ? '⚡' :
                     getFileExtension(file.name) === 'json' ? '📋' :
                     getFileExtension(file.name) === 'md' ? '📝' : '📄'}
                  </span>
                  <span className="file-name" title={file.name}>
                    {file.name.length > 15 ? `${file.name.substring(0, 12)}...` : file.name}
                  </span>
                </div>
              </div>
            ))
          )}
        </div>
      </div>

      {/* Code Display Area */}
      <div className="code-viewer-content flex-grow-1 d-flex flex-column">
        {/* File Header */}
        <div className="code-header p-2 bg-light border-bottom d-flex justify-content-between align-items-center">
          <div className="d-flex align-items-center">
            <span className="mr-2">
              {selectedFileName ? (
                <>
                  <span className="file-icon mr-2">
                    {getFileExtension(selectedFileName) === 'html' ? '🌐' :
                     getFileExtension(selectedFileName) === 'css' ? '🎨' :
                     getFileExtension(selectedFileName) === 'js' || getFileExtension(selectedFileName) === 'jsx' ? '⚡' :
                     getFileExtension(selectedFileName) === 'json' ? '📋' :
                     getFileExtension(selectedFileName) === 'md' ? '📝' : '📄'}
                  </span>
                  <strong>{selectedFileName}</strong>
                </>
              ) : (
                <span className="text-muted">Keine Datei ausgewählt</span>
              )}
            </span>
          </div>
          <div className="code-info">
            <small className="text-muted">
              {selectedFileContent ? (
                <>
                  {formattedLines.length} Zeilen | {language.toUpperCase()}
                </>
              ) : (
                'Leer'
              )}
            </small>
          </div>
        </div>

        {/* Code Content */}
        <div className="code-content flex-grow-1" style={{ overflow: 'auto', backgroundColor: '#fafafa' }}>
          {!selectedFileContent ? (
            <div className="d-flex align-items-center justify-content-center h-100">
              <div className="text-center text-muted">
                <div className="mb-3" style={{ fontSize: '3rem' }}>👁️</div>
                <h5>Code-Betrachter</h5>
                <p>Wähle eine Datei aus der Seitenleiste, um den Code zu betrachten.</p>
                <small>Generiere zuerst Code mit der KI, dann werden die Dateien hier angezeigt.</small>
              </div>
            </div>
          ) : (
            <div className="code-display">
              <table className="code-table w-100" style={{ fontFamily: 'Monaco, Consolas, "Courier New", monospace', fontSize: '13px' }}>
                <tbody>
                  {formattedLines.map((line, index) => (
                    <tr key={index} className={index % 2 === 0 ? 'code-line-even' : 'code-line-odd'}>
                      <td 
                        className="line-number text-right pr-3 text-muted" 
                        style={{ 
                          width: '50px', 
                          backgroundColor: '#f0f0f0', 
                          borderRight: '1px solid #ddd',
                          userSelect: 'none',
                          fontSize: '12px'
                        }}
                      >
                        {line.number}
                      </td>
                      <td 
                        className="line-content pl-3" 
                        style={{ backgroundColor: '#fafafa' }}
                        dangerouslySetInnerHTML={{ __html: line.content }}
                      />
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Inline Styles for syntax highlighting */}
      <style dangerouslySetInnerHTML={{__html: `
        .code-keyword { color: #0066cc; font-weight: bold; }
        .code-string { color: #008800; }
        .code-comment { color: #666666; font-style: italic; }
        .code-tag { color: #000080; font-weight: bold; }
        .code-attribute { color: #000080; }
        .code-selector { color: #0066cc; font-weight: bold; }
        .code-property { color: #000080; }
        .code-value { color: #008800; }
        .code-json-key { color: #000080; font-weight: bold; }
        
        .code-line-even { background-color: #fafafa; }
        .code-line-odd { background-color: #ffffff; }
        
        .code-table { border-collapse: collapse; }
        .code-table td { padding: 2px 0; vertical-align: top; }
        
        .file-item:hover {
          background-color: #e9ecef !important;
        }
        
        .cursor-pointer {
          cursor: pointer;
        }
      `}} />
    </div>
  )
}

export default CodeViewer
