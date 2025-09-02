# TODO: Moodle Module "Vibe Your Course"

## 📋 Projektübersicht
Entwicklung eines KI-gestützten Moodle-Plugins mit LTI-Integration und moderner SPA-Frontend-Architektur.

---

## 🎯 Phase 1: Grundlegendes Setup & Infrastruktur

### ✅ Abgeschlossen
- [x] Git-Repository und Submodule-Setup
- [x] Projekt-README mit ausführlicher Beschreibung

### 🔄 In Arbeit
- [ ] **Datenbank-Schema Design**
  - [ ] Haupttabellen für mod_vibeyourcourse definieren
  - [ ] Install/Upgrade SQL-Skripte erstellen
  - [ ] Backup/Restore Kompatibilität sicherstellen

### ⏳ Geplant
- [ ] **Plugin-Grundstruktur**
  - [ ] Moodle Plugin-Skeleton erstellen
  - [ ] Grundlegende Verzeichnisstruktur aufbauen
  - [ ] version.php und Plugin-Metadaten definieren

---

## 🎯 Phase 2: Core Plugin-Entwicklung

### 📁 Plugin-Architektur
- [ ] **Ordnerstruktur**
  ```
  mod/vibeyourcourse/
  ├── backup/               # Backup/Restore Funktionalität
  ├── classes/              # Core PHP Klassen
  │   ├── local/           # Lokale Klassen
  │   ├── output/          # Renderer Klassen
  │   └── privacy/         # GDPR/Privacy Klassen
  ├── db/                  # Datenbankdefinitionen
  ├── lang/en/             # Sprachdateien
  ├── pix/                 # Icons und Grafiken
  ├── templates/           # Mustache Templates
  └── tests/               # Unit/Integration Tests
  ```

### 🛠 Core-Komponenten
- [ ] **lib.php - Hauptfunktionen**
  - [ ] `vibeyourcourse_add_instance()`
  - [ ] `vibeyourcourse_update_instance()`
  - [ ] `vibeyourcourse_delete_instance()`
  - [ ] `vibeyourcourse_supports()` - Feature Support

- [ ] **mod_form.php - Konfigurationsformular**
  - [ ] Grundeinstellungen für Kursaktivität
  - [ ] KI-Prompt-Konfiguration
  - [ ] LTI-Tool-Settings

- [ ] **view.php - Hauptansicht**
  - [ ] LTI-Launch Mechanismus
  - [ ] Benutzer-Authentifizierung
  - [ ] Content-Rendering

### 🗃 Datenbank-Design
- [ ] **Haupttabelle: `mdl_vibeyourcourse`**
  ```sql
  - id (int, auto_increment)
  - course (int, foreign_key)
  - name (varchar)
  - intro (text)
  - introformat (int)
  - timecreated (int)
  - timemodified (int)
  - ai_config (text, JSON)
  ```

- [ ] **Benutzer-Interaktionen: `mdl_vibeyourcourse_sessions`**
  ```sql
  - id (int, auto_increment)
  - vibeyourcourse_id (int, foreign_key)
  - userid (int, foreign_key)
  - session_data (text, JSON)
  - created_at (int)
  - updated_at (int)
  ```

- [ ] **Generated Content: `mdl_vibeyourcourse_content`**
  ```sql
  - id (int, auto_increment)
  - session_id (int, foreign_key)
  - prompt_id (varchar)
  - content_type (varchar)
  - content_data (text, JSON)
  - generated_at (int)
  ```

---

## 🎯 Phase 3: LTI-Integration & Frontend

### 🔗 LTI Provider Setup
- [ ] **LTI-Tool Konfiguration**
  - [ ] LTI 1.3 Provider implementieren
  - [ ] Deep Linking Support
  - [ ] Grade Passback Integration
  - [ ] Security Implementation (OAuth, Signatures)

- [ ] **LTI Launch Handler**
  - [ ] Launch-URL Endpoint
  - [ ] Parameter Validation
  - [ ] User Context Mapping
  - [ ] Course Context Übertragung

### 🖥 Frontend SPA Development
- [ ] **Technologie-Stack festlegen**
  - [ ] Framework wählen (React/Vue/Angular)
  - [ ] Build-System konfigurieren
  - [ ] API-Client Setup

- [ ] **Core Components**
  - [ ] Authentication Component
  - [ ] Prompt Input Interface
  - [ ] Content Display Area
  - [ ] Progress Tracking
  - [ ] Settings Panel

### 📡 API-Endpoints
- [ ] **REST API für Frontend**
  - [ ] `/api/prompts` - Prompt Management
  - [ ] `/api/content` - Content CRUD
  - [ ] `/api/sessions` - Session Management
  - [ ] `/api/progress` - Progress Tracking

---

## 🎯 Phase 4: KI-Integration & Content-Generierung

### 🤖 KI-Service Integration
- [ ] **Prompt-System**
  - [ ] JSON-Blueprint Struktur definieren
  - [ ] Template Engine implementieren
  - [ ] Variable Substitution
  - [ ] Context-aware Prompting

- [ ] **Content Generation**
  - [ ] API-Integration (OpenAI/Claude/etc.)
  - [ ] Content Validation
  - [ ] Multi-Modal Support
  - [ ] Caching Strategy

### 📋 Prompt-Blueprints (JSON-Vorlagen)
- [ ] **Basis-Templates**
  ```json
  {
    "id": "basic_lesson",
    "name": "Grundlegende Lektion",
    "template": "Erstelle eine Lektion über {topic} für {target_audience}",
    "variables": ["topic", "target_audience"],
    "output_format": "structured_html"
  }
  ```

- [ ] **Spezielle Templates**
  - [ ] Quiz-Generierung
  - [ ] Case Studies
  - [ ] Interactive Scenarios
  - [ ] Assessment Rubrics

---

## 🎯 Phase 5: Testing & Quality Assurance

### 🧪 Testing Strategy
- [ ] **Unit Tests**
  - [ ] Plugin Core Functions
  - [ ] Database Operations
  - [ ] API Endpoints
  - [ ] LTI Integration

- [ ] **Integration Tests**
  - [ ] Moodle Integration
  - [ ] LTI Tool Compatibility
  - [ ] Cross-Browser Testing
  - [ ] Mobile Responsiveness

### 🔍 Code Quality
- [ ] **Code Standards**
  - [ ] Moodle Coding Guidelines
  - [ ] PHP CodeSniffer Integration
  - [ ] ESLint für Frontend
  - [ ] Documentation Standards

---

## 🎯 Phase 6: Deployment & Documentation

### 📦 Packaging & Distribution
- [ ] **Plugin Package**
  - [ ] ZIP-Distribution erstellen
  - [ ] Abhängigkeiten dokumentieren
  - [ ] Installation Guide
  - [ ] Upgrade Path definieren

### 📚 Dokumentation
- [ ] **Benutzer-Dokumentation**
  - [ ] Administrator Guide
  - [ ] Teacher Guide
  - [ ] Student Guide
  - [ ] Troubleshooting

- [ ] **Entwickler-Dokumentation**
  - [ ] API Documentation
  - [ ] Plugin Architecture
  - [ ] Extension Points
  - [ ] Contributing Guidelines

---

## 🏷 Labels & Prioritäten

**Priorität:**
- 🔥 **Kritisch** - Blockiert andere Entwicklung
- ⚡ **Hoch** - Wichtig für MVP
- 📋 **Normal** - Standard Feature
- 💡 **Nice-to-have** - Verbesserung

**Status:**
- ✅ **Abgeschlossen**
- 🔄 **In Arbeit**
- ⏳ **Geplant**
- 🚫 **Blockiert**
- 💭 **Brainstorming**

**Geschätzter Aufwand:**
- 🕐 **S** (1-2 Tage)
- 🕕 **M** (3-5 Tage)
- 🕘 **L** (1-2 Wochen)
- 🕛 **XL** (2+ Wochen)

---

## 📊 Meilensteine

### 🏁 Milestone 1: Plugin Foundation (Woche 1-2)
- Grundlegendes Plugin-Setup
- Datenbank-Schema
- Basis-Funktionalität

### 🏁 Milestone 2: LTI Integration (Woche 3-4)
- LTI Provider Implementation
- Frontend SPA Setup
- Basis API

### 🏁 Milestone 3: KI-Integration (Woche 5-6)
- Prompt-System
- Content Generation
- Template Engine

### 🏁 Milestone 4: Testing & Polish (Woche 7-8)
- Comprehensive Testing
- Performance Optimization
- Documentation
- Release Preparation

---

*Letzte Aktualisierung: $(date)*
*Status: Foundation Phase - Repository Setup abgeschlossen*
