# Moodle-Modul: Vibe Your Course
## KI-gestützte App-Entwicklung für Studierende 🚀

### 🔥 Die Vision: "Vibe Coding" - Von der Idee zur App durch natürliche Sprache

Vibe Your Course revolutioniert das Lernen durch **KI-gestützte App-Entwicklung**. Studierende beschreiben ihre App-Ideen in natürlicher Sprache, und die KI agiert als ihr persönlicher Entwickler, der lauffähige "Micro Apps" erstellt. Der Fokus liegt auf der **Idee, dem Konzept und dem kreativen Problemlösen** - nicht auf der Programmierung.

**"Erstelle eine To-Do-App für Studienorganisation"** → KI generiert vollständige Web-App → App läuft sofort im Browser

Studierende werden zu **App-Architekten**, die ihre Visionen durch intelligente Prompts Realität werden lassen. Sie lernen Softwaredesign, UX-Konzepte und Problemlösung, ohne von Syntax-Details abgelenkt zu werden.

---

## 🚀 Kernfunktionen

### ✅ Aktuell verfügbar (v1.0)

- **🤖 KI-Chat-Interface**: Intuitive Prompt-Eingabe für App-Beschreibungen in natürlicher Sprache
- **🌐 WebContainer-Integration**: Browser-API von StackBlitz für echte Node.js-Apps im Browser
- **💬 3-Tab-System**: Chat (KI-Kommunikation), WebContainer (App-Vorschau), Console (Output)
- **📊 Smart Fallback**: Automatische Fallback-Modi bei fehlender Cross-Origin-Isolation
- **🔧 Cross-Origin-Isolation**: Vollständige Header-Unterstützung für SharedArrayBuffer
- **🔧 Vollständige Moodle-Integration**: Nahtlose Integration in bestehende Kursstrukturen

### 🎯 Nächste Features (Roadmap)

- **🧠 Claude-API Integration**: Intelligente App-Generierung basierend auf Prompts
- **📱 Template-System**: Vorgefertigte App-Typen für verschiedene Anwendungsfälle  
- **🐍 Python-Apps**: Pyodide-Integration für datenanalytische Anwendungen
- **🎨 Erweiterte UI-Generierung**: Komplexere Apps mit modernen Frameworks

### 👥 Teilen, Entdecken und Kollaborieren

- **Projekt-Galerie**: Fertige Micro Apps können in einer kursweiten Galerie ausgestellt werden. Das schafft Anerkennung und motiviert.

- **Voneinander lernen**: Studierende können die Projekte ihrer Kommilitonen ansehen, ausprobieren und deren Code studieren. So entsteht ein organischer Wissensaustausch.

- **Peer-Feedback**: Geplante Features ermöglichen es, Feedback zu den Apps zu geben und gemeinsam an Lösungen zu arbeiten.

---

## 🔧 Moodle-Integration

- **Nahtlos und nativ**: "Vibe Your Course" ist eine normale Moodle-Aktivität und nutzt die bestehende Nutzer-, Kurs- und Bewertungsstruktur.

- **Didaktische Steuerung**: Lehrende behalten die volle Kontrolle über die Rahmenbedingungen, wie verfügbare Technologien (Python, Web), den Grad der KI-Unterstützung und Bewertungskriterien.

---

## 🎯 Zielgruppe

**Lehrende**: Wollen einen anwendungsorientierten Unterricht gestalten, der über die reine Wissensvermittlung hinausgeht und Kreativität fördert.

**Lernende**: Suchen nach einer motivierenden Möglichkeit, Gelerntes praktisch anzuwenden und sichtbare Ergebnisse zu schaffen, die sie teilen können.

---

## 🗺️ Entwicklungsstatus

### ✅ Abgeschlossene Phasen

- **Phase 1**: Moodle-Plugin-Fundament *(Abgeschlossen)*
  - Vollständige Datenbank-Integration
  - Modulkonfiguration und -verwaltung
  - Benutzer- und Kurskontext-Integration

- **Phase 2**: React SPA Frontend *(Abgeschlossen)*
  - Modernes 3-Tab-Interface (Chat, WebContainer, Console)
  - Responsive Design mit Bootstrap-Integration
  - Projekt-Management und -erstellung

- **Phase 3**: KI-Chat & WebContainer *(Abgeschlossen)*
  - Funktionsfähiges Chat-Interface für Prompts
  - AJAX-Endpunkte für Backend-Kommunikation
  - WebContainer Browser-API Integration (StackBlitz)
  - Cross-Origin-Isolation-Support für SharedArrayBuffer
  - Automatische Fallback-Modi bei fehlender Isolation

### 🔥 Nächste Phasen

- **Phase 4**: Intelligente App-Generierung
  - Claude-API Integration für echte KI-Funktionalität
  - Template-basierte App-Erstellung
  
- **Phase 5**: Erweiterte Features
  - Python-App-Support mit Pyodide
  - Projekt-Galerie und Sharing-Funktionen
  - Lernanalytik und Fortschritts-Tracking

---

## 🛠️ Entwicklung & Setup

### Quick Start
```bash
# 1. Frontend Dependencies installieren
cd mod/vibeyourcourse/frontend/
npm install

# 2. Production Build erstellen  
npm run build

# 3. WebContainer Setup (Cross-Origin-Isolation für Browser-API)
# Apache mod_headers aktivieren für SharedArrayBuffer
docker exec moodle-docker-webserver-1 a2enmod headers
docker exec moodle-docker-webserver-1 service apache2 reload

# 4. Test-Seite aufrufen (optional)
# http://localhost/mod/vibeyourcourse/test_headers.php
```

### Projektstruktur
```
mod/vibeyourcourse/
├── frontend/              # React Development Environment
│   ├── src/
│   │   ├── App.jsx       # Haupt React-Komponente
│   │   ├── WebContainer.jsx # WebContainer-Integration
│   │   ├── App.css       # UI-Styles
│   │   └── main.jsx      # React Entry Point
│   ├── vite.config.js    # Build-Konfiguration
│   └── package.json      # Dependencies (inkl. @webcontainer/api)
├── build/                # Compiled React App
│   ├── bundle.js        # Single JavaScript Bundle (~220kB)
│   ├── bundle.css       # Single CSS Bundle
│   └── index.html
├── ajax.php             # Backend-API für Chat & Datenbank
├── view.php             # PHP-Datei lädt React SPA (mit Cross-Origin-Headers)
├── .htaccess           # Cross-Origin-Header für WebContainer Browser-API
├── WEBCONTAINER_GUIDE.md # Vollständige WebContainer-Dokumentation
├── test_headers.php    # Diagnose-Tool für Cross-Origin-Isolation
└── TODO.md             # Aktueller Entwicklungsstand
```

### Funktionsweise
1. **Frontend**: React SPA mit 3-Tab-Interface (Chat, WebContainer, Console)
2. **Backend**: PHP AJAX-Endpunkte für Prompt-Verarbeitung und Datenbank-Logging
3. **WebContainer**: StackBlitz Browser-API für echte Node.js-Apps im Browser
4. **Cross-Origin-Isolation**: Headers für SharedArrayBuffer-Support
5. **Fallback**: Intelligente Fallback-Modi bei fehlender Isolation

### Moodle Integration
- Vollständige Integration in Moodle-Aktivitätssystem
- Nutzer-Context und Session-Management
- Kurs-spezifische Projekt-Verwaltung
- Datenbank-Logging aller KI-Interaktionen