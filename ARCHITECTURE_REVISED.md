# 🚀 Revised Architecture: Interactive Code Learning Platform

## 🎯 **Das wahre Ziel:**
**"Vibe Your Course"** = Studenten entwickeln **eigene lauffähige Apps** in einer sicheren Browser-Umgebung mit KI-Unterstützung.

---

## 🏗 **Neue Architektur (Code-Execution Platform)**

```
┌─────────────────────┐    ┌─────────────────────┐    ┌─────────────────────┐
│   Moodle Core       │    │  mod_vibeyourcourse │    │   Browser Runtime   │
│                     │◄──►│                     │◄──►│                     │
│ • User Management   │    │ • KI-Prompt System  │    │ • Pyodide (Python) │
│ • Course Context    │    │ • Code Generation   │    │ • WebContainer      │
│ • Grade Management  │    │ • Template Engine   │    │ • CodeMirror Editor │
│ • Progress Tracking │    │ • Security Layer    │    │ • Live App Preview │
└─────────────────────┘    └─────────────────────┘    └─────────────────────┘
                                      ↓
                           ┌─────────────────────┐
                           │   External AI APIs  │
                           │                     │
                           │ • OpenAI/Claude     │
                           │ • Code Generation   │
                           │ • Error Analysis    │
                           │ • Hints & Tips      │
                           └─────────────────────┘
```

---

## 🎮 **User Experience (Student Journey)**

### 1. **KI-Assisted App Ideation**
```
Student: "Ich will eine Todo-App lernen"
    ↓
KI generates: Structured learning path + code templates
    ↓
Platform: Creates executable project skeleton
```

### 2. **Interactive Development**
```
┌─────────────────────┐    ┌─────────────────────┐
│   Code Editor       │    │   Live Preview      │
│                     │    │                     │
│ • Syntax Highlight  │◄──►│ • Real-time Exec    │
│ • KI Autocomplete   │    │ • Error Display     │
│ • Smart Hints       │    │ • Visual Output     │
└─────────────────────┘    └─────────────────────┘
```

### 3. **"Vibe Coding" Features**
- 🤖 **KI Code Assistant**: Live coding help
- 🔄 **Real-time Execution**: Code runs sofort im Browser
- 🎯 **Progressive Challenges**: KI passt Schwierigkeit an
- 🏆 **Achievement System**: Gamification elements

---

## 🛠 **Technology Stack (Revised)**

### **Backend (Moodle Plugin)**
- **PHP 8.x** - Core logic & KI integration
- **Moodle APIs** - User, course, grading
- **OpenAI/Claude APIs** - Code generation & assistance
- **JSON Templates** - Learning scenarios & prompts

### **Frontend Runtime Environment**
- **Pyodide** - Python execution in browser
- **WebContainer** - Full web dev stack (Node.js, npm, etc.)
- **CodeMirror 6** - Advanced code editor
- **Monaco Editor** - VS Code-like experience (alternative)

### **Supported Runtimes**
```javascript
// WebContainer - für Web Development
const webcontainer = await WebContainer.boot();
await webcontainer.mount(files);
await webcontainer.spawn('npm', ['run', 'dev']);

// Pyodide - für Python
import { loadPyodide } from "pyodide";
const pyodide = await loadPyodide();
pyodide.runPython(`
    import pandas as pd
    # Student's code here
`);
```

---

## 📁 **Updated File Structure**

```
mod/vibeyourcourse/
├── version.php
├── lib.php                    # Core Moodle functions
├── view.php                   # Main IDE interface
├── ajax.php                   # API endpoints
├── classes/
│   ├── ai/
│   │   ├── code_generator.php     # KI code generation
│   │   ├── hint_provider.php      # Smart hints
│   │   └── error_analyzer.php     # Debug assistance
│   ├── runtime/
│   │   ├── project_manager.php    # Student projects
│   │   ├── execution_sandbox.php  # Security wrapper
│   │   └── progress_tracker.php   # Learning analytics
│   └── templates/
│       ├── python_app.php         # Python project templates
│       ├── web_app.php            # HTML/JS/CSS templates
│       └── data_science.php       # Jupyter-like templates
├── frontend/                  # SPA for the IDE
│   ├── src/
│   │   ├── components/
│   │   │   ├── CodeEditor.js      # CodeMirror integration
│   │   │   ├── RuntimePreview.js  # Pyodide/WebContainer
│   │   │   ├── AIAssistant.js     # KI chat interface
│   │   │   ├── FileExplorer.js    # Project files
│   │   │   └── ProgressPanel.js   # Learning progress
│   │   ├── runtime/
│   │   │   ├── pyodide-worker.js  # Python execution
│   │   │   ├── webcontainer.js    # Web dev stack
│   │   │   └── sandbox-manager.js # Security layer
│   │   └── ai/
│   │       ├── prompt-engine.js   # KI interaction
│   │       └── code-analyzer.js   # Code quality
│   ├── package.json
│   └── webpack.config.js
├── templates/
│   ├── ide_interface.mustache     # Main IDE layout
│   └── project_gallery.mustache  # Showcase
└── db/
    ├── install.xml               # Student projects DB
    └── upgrade.php
```

---

## 🔧 **Core Features Implementation**

### **1. KI-Powered Project Generation**
```php
class code_generator {
    public function create_app_from_prompt($user_prompt, $difficulty_level) {
        $prompt = "Create a {$difficulty_level} {$user_prompt} with:
        - Clean, commented code
        - Progressive learning steps
        - Interactive elements
        - Test cases";
        
        $ai_response = $this->call_ai_api($prompt);
        return $this->package_as_project($ai_response);
    }
}
```

### **2. Browser Code Execution**
```javascript
// Python Apps mit Pyodide
class PythonRuntime {
    async executeCode(code) {
        const pyodide = await loadPyodide();
        await pyodide.loadPackage(['pandas', 'matplotlib']);
        
        try {
            const result = pyodide.runPython(code);
            return { success: true, output: result };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

// Web Apps mit WebContainer
class WebRuntime {
    async createProject(template) {
        const webcontainer = await WebContainer.boot();
        await webcontainer.mount(template.files);
        
        const server = await webcontainer.spawn('npm', ['run', 'dev']);
        return server.url; // Live preview URL
    }
}
```

### **3. Real-time KI Assistant**
```javascript
class AICodeAssistant {
    async getCodeHelp(currentCode, cursor, userQuestion) {
        const context = this.analyzeCodeContext(currentCode, cursor);
        
        const response = await fetch('/mod/vibeyourcourse/ajax.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'get_code_help',
                code: currentCode,
                context: context,
                question: userQuestion
            })
        });
        
        return response.json(); // KI suggestions
    }
}
```

---

## 🎯 **Learning Scenarios**

### **Scenario 1: "Todo App Challenge"**
```json
{
    "title": "Interactive Todo App",
    "difficulty": "beginner",
    "runtime": "webcontainer",
    "template": {
        "index.html": "<!DOCTYPE html>...",
        "app.js": "// Your todo logic here",
        "style.css": "/* Make it beautiful */"
    },
    "challenges": [
        "Add new todos",
        "Mark as complete", 
        "Delete todos",
        "Save to localStorage",
        "Add animations"
    ],
    "ai_hints": true
}
```

### **Scenario 2: "Data Analysis Dashboard"**
```json
{
    "title": "Sales Data Dashboard",
    "difficulty": "intermediate", 
    "runtime": "pyodide",
    "packages": ["pandas", "plotly", "streamlit"],
    "template": {
        "main.py": "import pandas as pd\n# Analyze sales data",
        "data.csv": "date,product,sales\n..."
    },
    "challenges": [
        "Load CSV data",
        "Create visualizations", 
        "Filter by date range",
        "Export reports"
    ]
}
```

---

## 🚀 **Why This Architecture?**

### ✅ **Warum Pyodide/WebContainer NOW macht Sinn:**
- 🎯 **Student Code Execution**: Apps laufen echt im Browser
- 🔒 **Sandboxed Environment**: Sicher für Studenten-Code
- ⚡ **Real-time Feedback**: Sofortiges Testen & Debuggen
- 🎮 **Interactive Learning**: Hands-on coding experience
- 🤖 **KI Integration**: Live code assistance

### ✅ **Warum SPA Frontend NOW macht Sinn:**
- 🛠 **IDE-like Experience**: Professional development environment
- 🔄 **Real-time Collaboration**: Code sharing & review
- 📱 **Rich Interactions**: Drag & drop, split panes, etc.
- 🎨 **Modern UX**: VS Code-like interface

---

## 📈 **Implementation Phases (Revised)**

### **Phase 1: Core Plugin + Basic Editor (2 Wochen)**
- Moodle plugin structure
- Basic CodeMirror integration
- Simple Python execution (Pyodide)

### **Phase 2: KI Integration (2 Wochen)**  
- OpenAI/Claude API integration
- Code generation from prompts
- Smart hints & error analysis

### **Phase 3: Advanced Runtimes (2 Wochen)**
- WebContainer for web development
- File system management
- Live preview capabilities

### **Phase 4: Learning Features (1 Woche)**
- Progress tracking
- Achievement system
- Project gallery & sharing

---

**Das ist eine völlig andere Liga! 🎮 Ein echtes "Code Learning Playground" statt nur Content-Anzeige.**
