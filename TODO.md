# TODO: Interactive Code Learning Platform "Vibe Your Course"

## 🎯 **Vision: Students build & run real apps in browser**
KI-gestütztes Moodle-Plugin wo Studenten **eigene lauffähige Anwendungen** entwickeln können - von Todo-Apps bis Data Science Dashboards.

---

## �� **Architektur-Überblick**

```
Moodle Plugin ◄─► KI APIs ◄─► Browser Runtime (Pyodide/WebContainer)
     ↓               ↓                    ↓
• User Management   • Code Gen       • Python/JS Execution  
• Progress Track    • Hints & Tips   • Live App Preview
• Grading          • Error Analysis  • File System
```

---

## 🎯 Phase 1: Foundation & Basic Code Execution (2-3 Wochen)

### ✅ Abgeschlossen
- [x] Git-Repository und Submodule-Setup
- [x] Projekt-README mit Beschreibung
- [x] Architektur-Dokumentation (revised)

### 🔄 In Arbeit - Moodle Plugin Core
- [ ] **🏗 Basis Plugin-Struktur**
  - [ ] `version.php` - Plugin metadata
  - [ ] `lib.php` - Core Moodle functions  
  - [ ] `mod_form.php` - Activity settings
  - [ ] `view.php` - Main IDE interface
  - [ ] `ajax.php` - API endpoints

- [ ] **🗃 Datenbank-Schema**
  ```sql
  mdl_vibeyourcourse:
  - id, course, name, intro
  - ai_config (JSON)
  - allowed_runtimes (JSON: python, web, etc.)
  
  mdl_vibeyourcourse_projects:
  - id, vibeyourcourse_id, userid
  - project_name, runtime_type
  - files (JSON), last_modified
  
  mdl_vibeyourcourse_executions:
  - id, project_id, code, output
  - execution_time, success
  ```

### ⏳ Geplant - Frontend IDE
- [ ] **🎨 SPA Frontend Setup**
  - [ ] React/Vue app in `frontend/` folder
  - [ ] Webpack build pipeline
  - [ ] Integration with Moodle
  - [ ] Responsive IDE layout

- [ ] **💻 Code Editor Integration**
  - [ ] CodeMirror 6 setup
  - [ ] Syntax highlighting (Python, JS, HTML, CSS)
  - [ ] Auto-completion
  - [ ] Error highlighting

- [ ] **🐍 Pyodide Integration (Python Runtime)**
  - [ ] Pyodide worker setup
  - [ ] Package management (pandas, numpy, matplotlib)
  - [ ] Code execution & output capture
  - [ ] Error handling & display

---

## 🎯 Phase 2: KI-Integration & Code Generation (2 Wochen)

### 🤖 KI-Powered Features
- [ ] **Code Generation Engine**
  ```php
  class ai_code_generator {
      public function generate_app($prompt, $difficulty, $runtime) {
          // "Create a todo app for beginners"
          // → Full project with HTML/JS/CSS
      }
      
      public function provide_hint($code, $error, $context) {
          // Analyze current code + error
          // → Specific help suggestion
      }
  }
  ```

- [ ] **Smart Templates System**
  ```json
  {
      "python_data_analysis": {
          "template": "import pandas as pd\n# {task_description}",
          "packages": ["pandas", "matplotlib", "seaborn"],
          "challenges": ["Load data", "Create plots", "Export results"]
      },
      "web_todo_app": {
          "files": {
              "index.html": "<!DOCTYPE html>...",
              "app.js": "// Todo logic here",
              "style.css": "/* Beautiful styling */"
          },
          "challenges": ["Add todos", "Mark complete", "Persist data"]
      }
  }
  ```

- [ ] **Real-time AI Assistant**
  - [ ] Chat interface in IDE
  - [ ] Context-aware code help
  - [ ] Error explanation & fixes
  - [ ] Learning progress adaptation

### 🔌 API Integration
- [ ] **OpenAI/Claude Integration**
  - [ ] Secure API key management
  - [ ] Code-specific prompts
  - [ ] Rate limiting & caching
  - [ ] Fallback strategies

---

## 🎯 Phase 3: Advanced Runtimes & Features (2 Wochen)

### 🌐 WebContainer Integration (Web Development)
- [ ] **Full Web Stack in Browser**
  ```javascript
  const webcontainer = await WebContainer.boot();
  
  // Student creates React/Vue/Node.js apps
  await webcontainer.spawn('npm', ['create', 'react-app', 'my-app']);
  await webcontainer.spawn('npm', ['run', 'dev']);
  
  // Live preview URL for student's app
  const preview = await webcontainer.preview;
  ```

- [ ] **Supported Frameworks**
  - [ ] Vanilla HTML/JS/CSS
  - [ ] React applications
  - [ ] Vue.js projects  
  - [ ] Node.js backends
  - [ ] Express servers

### 📊 Advanced Python Features
- [ ] **Data Science Stack**
  - [ ] Jupyter-like notebook interface
  - [ ] Interactive plots (Plotly)
  - [ ] Data upload & processing
  - [ ] Export capabilities

- [ ] **Machine Learning Support**
  - [ ] Scikit-learn integration
  - [ ] Simple ML model training
  - [ ] Visualization of results

### 🎮 Interactive Learning Features
- [ ] **Progressive Challenges**
  - [ ] Step-by-step coding tasks
  - [ ] Automated testing/validation
  - [ ] Hint system
  - [ ] Achievement unlocks

- [ ] **Project Gallery**
  - [ ] Student project showcase
  - [ ] Code sharing & review
  - [ ] Collaborative features
  - [ ] Teacher feedback system

---

## 🎯 Phase 4: Learning Analytics & Gamification (1 Woche)

### 📈 Progress Tracking
- [ ] **Learning Analytics**
  ```php
  class learning_analytics {
      public function track_coding_session($project_id, $actions) {
          // Time spent coding
          // Errors encountered & resolved
          // KI hints used
          // Challenges completed
      }
      
      public function generate_progress_report($user_id) {
          // Skills development graph
          // Areas for improvement
          // Suggested next challenges
      }
  }
  ```

- [ ] **Adaptive Difficulty**
  - [ ] Performance-based challenge adjustment
  - [ ] Personalized learning paths
  - [ ] Smart content recommendations

### 🏆 Gamification System
- [ ] **Achievement System**
  - [ ] "First Python Script" badge
  - [ ] "Web Dev Master" achievement
  - [ ] "Bug Squasher" for error fixes
  - [ ] "AI Collaborator" for KI usage

- [ ] **Leaderboards & Social**
  - [ ] Course-wide coding challenges
  - [ ] Peer code reviews
  - [ ] Collaboration features

---

## 🎯 Phase 5: Production & Performance (1 Woche)

### ⚡ Performance Optimization
- [ ] **Code Execution Optimization**
  - [ ] Worker thread management
  - [ ] Memory usage optimization
  - [ ] Execution time limits
  - [ ] Resource monitoring

- [ ] **Frontend Performance**
  - [ ] Code splitting & lazy loading
  - [ ] IDE responsiveness
  - [ ] Large file handling
  - [ ] Mobile optimization

### 🔒 Security & Compliance
- [ ] **Sandbox Security**
  - [ ] Code execution isolation
  - [ ] Network access restrictions
  - [ ] File system limitations
  - [ ] Malicious code detection

- [ ] **Data Privacy**
  - [ ] GDPR compliance
  - [ ] Student data protection
  - [ ] Code ownership rights
  - [ ] Secure KI API usage

### 🧪 Testing & QA
- [ ] **Comprehensive Testing**
  - [ ] Unit tests (PHP backend)
  - [ ] Frontend component tests
  - [ ] Integration tests (Moodle)
  - [ ] Runtime execution tests
  - [ ] Performance benchmarks

---

## 🛠 **Konkrete Learning Scenarios**

### 🐍 **Python Learning Path**
1. **"Hello Analytics"** → Load CSV, create basic plot
2. **"Data Detective"** → Pandas filtering & analysis  
3. **"Visualization Master"** → Interactive dashboards
4. **"ML Explorer"** → First machine learning model

### 🌐 **Web Development Path**
1. **"Static Site"** → HTML/CSS basics
2. **"Interactive Page"** → JavaScript functionality
3. **"Todo Application"** → Full CRUD app
4. **"React Component"** → Modern framework usage

### 🎯 **Project-Based Challenges**
```json
{
    "financial_calculator": {
        "description": "Build a compound interest calculator",
        "runtime": "web",
        "difficulty": "beginner",
        "estimated_time": "45 minutes",
        "skills": ["JavaScript", "HTML Forms", "Math Functions"],
        "ai_starter_prompt": "Create a financial calculator that helps students understand compound interest"
    },
    
    "weather_dashboard": {
        "description": "Analyze weather data with Python",
        "runtime": "python", 
        "difficulty": "intermediate",
        "estimated_time": "90 minutes",
        "skills": ["Pandas", "APIs", "Data Visualization"],
        "ai_starter_prompt": "Build a weather analysis tool using real climate data"
    }
}
```

---

## 📊 **Success Metrics**

### Student Engagement
- [ ] Time spent coding per session
- [ ] Number of completed projects
- [ ] KI interaction frequency
- [ ] Peer code sharing rate

### Learning Outcomes  
- [ ] Skill progression tracking
- [ ] Error resolution improvement
- [ ] Code quality metrics
- [ ] Creative project complexity

### Technical Performance
- [ ] Code execution latency < 2s
- [ ] IDE responsiveness < 100ms
- [ ] 99% uptime for core features
- [ ] Mobile usability score > 85

---

## 🏷 **Priority Labels**

**🔥 Critical** - Core functionality
**⚡ High** - Key learning features  
**📋 Normal** - Enhancement features
**💡 Nice-to-have** - Advanced features

**🕐 S** (1-2 days) **🕕 M** (3-5 days) **🕘 L** (1-2 weeks) **🕛 XL** (2+ weeks)

---

## 🎯 **Next Immediate Actions**

1. **🔥 Create basic Moodle plugin structure** (🕐 S)
2. **🔥 Setup database schema** (🕐 S)  
3. **⚡ Integrate CodeMirror editor** (🕕 M)
4. **⚡ Basic Pyodide Python execution** (🕕 M)
5. **📋 KI code generation prototype** (🕘 L)

---

*"From idea to running app in minutes, not months"* 🚀

*Status: Architecture Revised - Ready for hands-on development*
*Last Updated: $(date)*
