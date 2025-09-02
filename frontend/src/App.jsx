import { useState, useEffect } from 'react'
import './App.css'

function App() {
  const [config, setConfig] = useState(null)
  const [projects, setProjects] = useState([])
  const [currentView, setCurrentView] = useState('projects') // 'projects' or 'ide'
  const [currentProject, setCurrentProject] = useState(null)
  const [showModal, setShowModal] = useState(false)
  const [isCreatingProject, setIsCreatingProject] = useState(false)
  const [newProject, setNewProject] = useState({
    name: '',
    runtime: 'python',
    template: ''
  })

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
        setNewProject({ name: '', runtime: 'python', template: '' })
        
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

  const openProject = (projectId) => {
    setCurrentProject(projectId)
    setCurrentView('ide')
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
                          onClick={() => openProject(project.id)}
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
              {/* File Explorer */}
              <div className="col-md-2 ide-sidebar">
                <h6>Files</h6>
                <div id="file-explorer">
                  {/* Files will be loaded here */}
                </div>
              </div>

              {/* Code Editor */}
              <div className="col-md-6 ide-editor">
                <div id="code-editor" style={{ height: '400px', border: '1px solid #ddd' }}>
                  {/* CodeMirror will be initialized here */}
                </div>
              </div>

              {/* Output/Preview */}
              <div className="col-md-4 ide-output">
                <ul className="nav nav-tabs">
                  <li className="nav-item">
                    <a className="nav-link active">Console</a>
                  </li>
                  <li className="nav-item">
                    <a className="nav-link">Preview</a>
                  </li>
                  {config?.aiAssistanceLevel !== 'none' && (
                    <li className="nav-item">
                      <a className="nav-link">AI Assistant</a>
                    </li>
                  )}
                </ul>
                <div className="tab-content">
                  <div className="tab-pane fade show active">
                    <div className="console"></div>
                  </div>
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
                    {config?.allowedRuntimes?.map((runtime) => (
                      <option key={runtime} value={runtime}>{runtime}</option>
                    )) || [
                      <option key="python" value="python">Python</option>,
                      <option key="javascript" value="javascript">JavaScript</option>
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
