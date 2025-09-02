# Moodle-Modul: Vibe Your Course
## Vom Lernstoff zur eigenen Micro App

### 🔥 Die Vision: Aktives Schaffen statt passives Lernen

Vibe Your Course revolutioniert das Lernen in Moodle. Statt Studierende nur mit Lernmaterial zu versorgen, geben wir ihnen ein Werkzeug an die Hand, mit dem sie ihr Wissen direkt anwenden und kreativ werden können. Das Ziel ist nicht das Coden selbst, sondern die Entwicklung eigener, lauffähiger "Micro Apps", die auf den Kursinhalten basieren.

Studierende lernen am besten, indem sie schaffen. Sie bauen einen Finanzrechner, nachdem sie die Zinsrechnung gelernt haben. Sie entwickeln ein interaktives Daten-Dashboard, nachdem sie statistische Grundlagen verstanden haben. So wird Wissen lebendig, greifbar und nachhaltig.

---

## 🚀 Kernfunktionen

### Von der Idee zur lauffähigen App im Browser

- **Echte Anwendungsentwicklung**: Studierende arbeiten nicht mit Code-Schnipseln, sondern bauen vollständige Kleinanwendungen. Dank Pyodide (Python) und WebContainern (HTML/JS/Node.js) laufen diese Apps direkt im Browser – ohne komplexe Installationen.

- **Lernmaterial als Startpunkt**: Lehrende definieren Projektvorlagen, die direkt an die Kursinhalte anknüpfen und den Studierenden den perfekten Einstieg in ihre eigene App-Entwicklung geben.

- **KI als kreativer Partner**: Der integrierte KI-Assistent hilft nicht nur bei Fehlern, sondern agiert als Sparringspartner, der hilft, Ideen zu konkretisieren und aus einem einfachen Konzept ("Eine App zur Visualisierung von Wahlergebnissen") eine strukturierte Projektbasis zu schaffen.

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

## 🗺️ Roadmap

- **Phase 1**: Fundament des Moodle-Plugins mit Datenbank und Konfiguration. *(Abgeschlossen)*

- **Phase 2**: React SPA Frontend und interaktive UI. *(Abgeschlossen)*

- **Phase 3**: Code-Editor Integration und API-Endpunkte. *(In Arbeit)*

- **Phase 4**: Integration der Browser-Runtimes (Pyodide, WebContainer) und der Sharing-Funktionen (Projekt-Galerie).

- **Phase 5**: Ausbau von Lernanalytik und Gamification.

- **Phase 6**: Optimierung und Stabilisierung für den produktiven Einsatz.

---

## 🛠️ Entwicklung & Build

### React Frontend Setup
Das Plugin nutzt ein modernes React Frontend mit Vite als Build-Tool:

```bash
# In das Frontend-Verzeichnis wechseln
cd frontend/

# Dependencies installieren
npm install

# Development Server für Frontend-Entwicklung
npm run dev

# Production Build erstellen
npm run build
```

### Projektstruktur
```
mod/vibeyourcourse/
├── frontend/           # React Development Environment
│   ├── src/
│   │   ├── App.jsx    # Haupt React-Komponente
│   │   ├── App.css    # Alle UI-Styles
│   │   └── main.jsx   # React Entry Point
│   ├── vite.config.js # Build-Konfiguration
│   └── package.json
├── build/             # Compiled React App
│   ├── bundle.js     # Single JavaScript Bundle
│   ├── bundle.css    # Single CSS Bundle
│   └── index.html
└── view.php          # PHP-Datei lädt React SPA
```

### Moodle Integration
Die `view.php` erstellt ein `<div id="vibeyourcourse-react-app"></div>` Container und lädt die kompilierten React-Bundles. Alle Moodle-spezifischen Daten (User-ID, Kurs-Context, etc.) werden über `window.vibeyourcourseConfig` an die React-App übergeben.