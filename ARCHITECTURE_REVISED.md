# 🚀 Überarbeitete Architektur: Vibe Your Course

## 🎯 Das wahre Ziel: Vom Lerninhalt zur geteilten "Micro App"

"Vibe Your Course" verwandelt Moodle in einen kreativen Schaffensraum. Studierende wenden Gelerntes direkt an, indem sie eigene lauffähige "Micro Apps" in einer sicheren Browser-Umgebung entwickeln. Diese Ergebnisse können sie anschließend mit ihren Kommilitonen teilen, um voneinander zu lernen und Feedback zu erhalten.

---

## 🏗 Architektur (Code-Execution & Collaboration Platform)

```
┌─────────────────────┐    ┌─────────────────────┐    ┌─────────────────────┐
│   Moodle Core       │    │  mod_vibeyourcourse │    │   Browser Runtime   │
│ (Nutzer, Kurse,     │◄──►│  (Projekt-Mgmt,     │◄──►│  (Pyodide für Python │
│  Bewertungen)       │    │   KI-Logik, UI,     │    │   WebContainer für  │
│                     │    │   Projekt-Galerie)  │    │   Web-Apps)         │
└─────────────────────┘    └─────────────────────┘    └─────────────────────┘
                                      ↓
                           ┌─────────────────────┐
                           │   Externe KI-APIs   │
                           │   (OpenAI/Claude)   │
                           └─────────────────────┘
```

---

## 🎮 User Experience (Student Journey)

### 1. Vom Kurs zur Projekt-Idee
- **Lehrender** stellt Vorlage "Datenanalyse" bereit
- **Student**: "Ich will die Verkaufszahlen aus der Vorlesung visualisieren"
- **Plattform**: Erstellt ein ausführbares Python-Projekt-Skelett mit den Beispieldaten

### 2. Interaktive App-Entwicklung
```
┌─────────────────────┐    ┌─────────────────────┐
│   Code Editor       │    │   Live App-Vorschau │
│ (mit KI-Hilfe)      │◄──►│ (Echtzeit-Ausführung)│
└─────────────────────┘    └─────────────────────┘
```

### 3. Teilen und Entdecken
- **Student** stellt fertige Dashboard-App in der Galerie aus
- **Kommilitonen** können die App nutzen, den Code ansehen und Feedback geben

---

## 🛠 Technology Stack (Revised)

### Backend (Moodle Plugin)
- **PHP 8.x** mit Moodle APIs
- **Externe KI-APIs** (OpenAI/Claude)

### Frontend IDE  
- **Single Page Application** (z.B. React/Vue) mit CodeMirror 6

### Browser Runtimes
- **Pyodide** (Python)
- **WebContainer** (Node.js, npm für Web-Apps)

---

## 🚀 Warum diese Architektur?

### ✅ Fokus auf Anwendung
Ermöglicht die Erstellung echter, lauffähiger Apps statt isolierter Code-Übungen.

### ✅ Sicherheit & Einfachheit
Die gesamte Ausführung findet in einer sicheren Sandbox im Browser statt. Keine Server-Wartung für Lehrende.

### ✅ Soziales Lernen
Die Projekt-Galerie fördert den Austausch und das Lernen von Peers.

### ✅ Moderne User Experience
Eine reaktive Single Page Application bietet ein flüssiges, desktop-ähnliches Gefühl direkt in Moodle.

---

**Das ist die Vision: Ein lebendiger Ort des Schaffens, nicht nur des Konsumierens.**