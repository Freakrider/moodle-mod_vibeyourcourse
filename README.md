# Moodle Module: Vibe Your Course

## Projektbeschreibung

**Vibe Your Course** ist ein innovatives Moodle-Plugin (`mod_vibe`), das eine KI-gestützte, personalisierte Lernumgebung schafft. Das System kombiniert moderne Web-Technologien mit intelligenter Content-Generierung, um ein immersives und adaptives Lernerlebnis zu bieten.

### Kernfunktionen

#### 🤖 KI-gestütztes Content Management
- **Intelligente Prompt-Verarbeitung**: Dynamische Generierung von Lerninhalten basierend auf Benutzer-Prompts
- **Adaptive Inhaltsanpassung**: Personalisierung der Lerninhalte je nach Lernfortschritt und -stil
- **Multi-Modal Content**: Unterstützung für Text, Bilder, Videos und interaktive Elemente

#### 🎨 Moderne Frontend-Architektur
- **LTI-Renderer SPA**: Single Page Application als LTI-Tool für nahtlose Moodle-Integration
- **Responsive Design**: Optimiert für Desktop, Tablet und Mobile
- **Real-time Updates**: Live-Aktualisierung von Inhalten ohne Seitenneuladen

#### 🛠 Technische Features
- **Clientseitiges Setup**: Einfache Installation und Konfiguration
- **JSON-basierte Prompt-Blueprints**: Strukturierte Vorlagen für Content-Generierung
- **Flexible Plugin-Architektur**: Erweiterbar durch Module und Add-ons
- **Datenbankintegration**: Effiziente Speicherung von Benutzer-Daten und generierten Inhalten

### Architektur-Überblick

```
┌─────────────────────┐    ┌─────────────────────┐    ┌─────────────────────┐
│   Moodle Core       │    │   mod_vibe Plugin   │    │   LTI-Renderer SPA │
│                     │◄──►│                     │◄──►│                     │
│ • Benutzerauthentifiz│    │ • Plugin Logic      │    │ • React/Vue Frontend│
│ • Kursverwaltung    │    │ • DB Management     │    │ • KI-Integration    │
│ • Grade Management  │    │ • LTI Provider      │    │ • Content Rendering │
└─────────────────────┘    └─────────────────────┘    └─────────────────────┘
```

### Zielgruppe
- **Lehrende**: Einfache Erstellung von personalisierten Kursinhalten
- **Lernende**: Immersive und adaptive Lernerfahrung
- **Administratoren**: Umfassende Kontrolle und Konfigurationsmöglichkeiten

### Roadmap
1. **Phase 1**: Grundlegendes Plugin-Setup und DB-Schema
2. **Phase 2**: LTI-Integration und Frontend-SPA
3. **Phase 3**: KI-Integration und Prompt-System
4. **Phase 4**: Advanced Features und Performance-Optimierung
