# TODO: Vibe Your Course – Interaktive Lernplattform

## 🎯 Vision
**Studierende wenden Gelerntes an und entwickeln eigene Micro Apps**

Dieses KI-gestützte Moodle-Plugin transformiert das Lernen. Studierende konsumieren nicht nur Inhalte, sondern werden zu Entwicklern. Sie nutzen das Lehrmaterial als Sprungbrett, um eigene lauffähige Anwendungen zu bauen – von interaktiven Datenvisualisierungen bis zu kleinen Web-Tools. Diese Projekte können sie teilen und zur Diskussion stellen.

---

## 🏗 Architektur-Überblick

```
Moodle Plugin ◄─► KI APIs ◄─► Browser Runtime (Pyodide/WebContainer)
     ↓               ↓                    ↓
• User Management   • App-Skelette   • Python/JS Execution
• Lernfortschritt   • Kreativ-Partner  • Live App Preview
• Projekt-Galerie   • Fehler-Analyse   • Virtuelles Dateisystem
```

---

## 🎯 Phase 1: Fundament & Plugin-Struktur
### ✅ Abgeschlossen

- [x] Git-Repository und Projekt-Struktur
- [x] Vision in README.md und ARCHITECTURE_REVISED.md geschärft
- [x] **🏗 Basis Plugin-Struktur**
  - [x] `version.php`, `lib.php`, `db/access.php`
  - [x] `mod_form.php` für detaillierte Aktivitätseinstellungen
- [x] **🗃 Umfassendes Datenbank-Schema** (`db/install.xml`)
  - [x] Tabellen für Projekte, Ausführungen, KI-Interaktionen und Lernanalysen
- [x] **🎨 UI-Grundgerüst** in `view.php`
  - [x] Ansicht für "Meine Projekte" und Modal zur Projekterstellung
  - [x] Layout-Platzhalter für die IDE (Editor, Dateiexplorer, Vorschau, KI-Chat)

---

## 🎯 Phase 2: Interaktive IDE & KI-Anbindung
### 🚧 In Arbeit

- [ ] **🎨 SPA Frontend Setup** (z.B. mit React/Vue)
- [ ] **💻 Code-Editor-Integration** (CodeMirror 6)
- [ ] **🔌 AJAX-Endpunkte** (`ajax.php`) für Speichern/Laden von Projekten
- [ ] **🤖 KI-Anbindung** für den KI-Assistenten-Chat und erste Code-Hilfen

---

## 🎯 Phase 3: App-Ausführung & Sharing
### 📋 Geplant

### Browser Runtime Integration
- [ ] **🐍 Pyodide Integration** für die Ausführung von Python-basierten Micro Apps
- [ ] **🌐 WebContainer Integration** für die Ausführung von Web-basierten Micro Apps (HTML/JS/CSS)

### Projekt-Galerie
- [ ] **🖼️ Project Gallery** - UI zur Anzeige und zum Durchsuchen der von Studierenden erstellten Apps
- [ ] **📤 Veröffentlichen** - Funktion zum "Veröffentlichen" eines Projekts in der Galerie

### Code Sharing & Review
- [ ] **👀 Projekt-Ansicht** - Ansicht zur Betrachtung der Projekte anderer
- [ ] **💬 Peer-Feedback** - Basis-Kommentarfunktion für Feedback

---

## 🎯 Phase 4: Lernanalytik & Gamification
### 📋 Geplant

- [ ] **📈 Lernanalytik-Dashboard** für Lehrende
- [ ] **🏆 Gamification-System** (Badges für "Erste App veröffentlicht", "Hilfreiches Feedback gegeben" etc.)

---

## 🔥 Nächste unmittelbare Aktionen

### Hohe Priorität
1. **Frontend-Framework (SPA)** aufsetzen und in `view.php` integrieren *(Aufwand: M)*
2. **CodeMirror-Editor** implementieren und mit `ajax.php` verbinden, um Projekt-Dateien zu speichern *(Aufwand: M)*
3. **Pyodide-Integration** prototypisch umsetzen, um Python-Code aus dem Editor auszuführen und die Ausgabe anzuzeigen *(Aufwand: M)*