# WebContainer Integration Guide

## Was ist WebContainer?

WebContainer ist eine **Browser-API** von StackBlitz, die es ermöglicht, vollständige Node.js-Umgebungen direkt im Browser auszuführen. Es ist **KEIN** Docker-Container, sondern eine JavaScript-API.

### Wichtige Konzepte:

- ✅ **Browser-API**: Läuft komplett im Browser
- ✅ **Node.js im Browser**: Echte Node.js-Umgebung ohne Server
- ✅ **File System**: Virtuelles Dateisystem im Browser
- ✅ **NPM Support**: Volle NPM-Integration
- ❌ **Kein Docker**: Hat nichts mit Container-Technologie zu tun
- ❌ **Kein Server**: Läuft nicht auf dem Server

## Grundlegende Implementierung

### 1. Installation

```bash
npm i @webcontainer/api
```

### 2. WebContainer Instance erstellen

```javascript
import { WebContainer } from '@webcontainer/api';

// WICHTIG: Kann nur EINMAL aufgerufen werden!
const webcontainerInstance = await WebContainer.boot();
```

### 3. Cross-Origin-Isolation

WebContainer benötigt SharedArrayBuffer, was Cross-Origin-Isolation erfordert:

```yaml
Cross-Origin-Embedder-Policy: require-corp
Cross-Origin-Opener-Policy: same-origin
```

**Für Moodle-Umgebung bereits implementiert in:**
- `view.php` - PHP-Header
- `.htaccess` - Apache-Header

### 4. Dateisystem verwenden

```javascript
// Dateien mounten
await webcontainerInstance.mount({
  'package.json': {
    file: {
      contents: JSON.stringify({
        name: 'my-app',
        scripts: {
          dev: 'node server.js'
        }
      })
    }
  },
  'server.js': {
    file: {
      contents: `
        const http = require('http');
        const server = http.createServer((req, res) => {
          res.writeHead(200, { 'Content-Type': 'text/html' });
          res.end('<h1>Hello WebContainer!</h1>');
        });
        server.listen(3000);
      `
    }
  }
});
```

### 5. Prozesse starten

```javascript
// NPM install ausführen
const installProcess = await webcontainerInstance.spawn('npm', ['install']);
await installProcess.exit;

// Dev-Server starten
await webcontainerInstance.spawn('npm', ['run', 'dev']);
```

### 6. Preview URL erhalten

```javascript
// Event-Listener für Server-Ready
webcontainerInstance.on('server-ready', (port, url) => {
  console.log(`Server bereit: ${url}`);
  // iframe.src = url;
});
```

## Für Vibe Your Course

### Aktueller Status

✅ **Implementiert:**
- Dynamisches Laden der WebContainer API
- Cross-Origin-Headers gesetzt
- Fallback-Modus bei fehlender Isolation
- Basis Hello World Projekt

### Nächste Schritte

1. **Frontend Build aktualisieren**
   ```bash
   cd frontend
   npm install @webcontainer/api
   npm run build
   ```

2. **Testen in Browser**
   - Öffne Vibe Your Course Aktivität
   - Erstelle neues Projekt
   - Wechsle zu WebContainer-Tab
   - Console sollte zeigen: `crossOriginIsolated: true`

### Debugging

**Browser-Konsole prüfen:**
```javascript
console.log('SharedArrayBuffer:', typeof SharedArrayBuffer !== 'undefined');
console.log('crossOriginIsolated:', crossOriginIsolated);
```

**Test-Seite verwenden:**
```
http://localhost/mod/vibeyourcourse/test_headers.php
```

## Erweiterte Features

### File System API

```javascript
// Datei schreiben
await webcontainerInstance.fs.writeFile('/path/file.js', 'content');

// Datei lesen
const content = await webcontainerInstance.fs.readFile('/path/file.js', 'utf8');

// Verzeichnis erstellen
await webcontainerInstance.fs.mkdir('/new-dir');

// Verzeichnis lesen
const files = await webcontainerInstance.fs.readdir('/');
```

### Terminal Integration

```javascript
// Shell-Prozess starten
const shellProcess = await webcontainerInstance.spawn('bash');

// Input senden
shellProcess.input.getWriter().write('ls -la\n');

// Output lesen
shellProcess.output.pipeTo(new WritableStream({
  write(data) {
    console.log(data);
  }
}));
```

## Wichtige Limitierungen

1. **Nur HTTPS in Produktion**: localhost ist OK, deployed Apps brauchen HTTPS
2. **Ein Instance pro Page**: `WebContainer.boot()` kann nur einmal aufgerufen werden
3. **Browser-Support**: Moderne Browser erforderlich (Chrome 88+, Firefox 79+)
4. **SharedArrayBuffer**: Benötigt Cross-Origin-Isolation

## Ressourcen

- [Offizielle Dokumentation](https://webcontainer.io/guides/quickstart)
- [API Reference](https://webcontainer.io/api)
- [StackBlitz Tutorial](https://stackblitz.com/docs)
- [Community Projects](https://webcontainer.io/community-projects)
