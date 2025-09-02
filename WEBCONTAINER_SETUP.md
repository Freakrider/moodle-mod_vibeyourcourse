# WebContainer Setup für Vibe Your Course

## Cross-Origin-Isolation Problem

WebContainer benötigt Cross-Origin-Isolation, um SharedArrayBuffer zu verwenden. Dies erfordert spezielle HTTP-Header.

## Lösungsansätze

### 1. Apache-Setup (.htaccess bereits erstellt)

Die `.htaccess`-Datei im Plugin-Verzeichnis setzt die notwendigen Header:

```apache
Cross-Origin-Embedder-Policy: require-corp
Cross-Origin-Opener-Policy: same-origin
```

### 2. Nginx-Setup (Alternative)

Für Nginx-Server folgende Header in der Server-Konfiguration hinzufügen:

```nginx
add_header Cross-Origin-Embedder-Policy "require-corp";
add_header Cross-Origin-Opener-Policy "same-origin";
```

### 3. Docker/Moodle-Setup

Für die moodle-docker-Umgebung:

1. **Apache mod_headers aktivieren:**
   ```bash
   docker exec moodle-docker-webserver-1 a2enmod headers
   docker exec moodle-docker-webserver-1 service apache2 reload
   ```

2. **Alternative: Custom Apache-Konfiguration:**
   Erstelle eine Datei `docker-compose.override.yml`:
   ```yaml
   version: '3'
   services:
     webserver:
       volumes:
         - ./custom-apache.conf:/etc/apache2/conf-available/vibeyourcourse.conf
   ```

### 4. Fallback-Modus

Wenn WebContainer nicht funktioniert, aktiviert sich automatisch der Fallback-Modus:

- ✅ Chat-Interface funktioniert vollständig
- ✅ Statische HTML-Vorschau
- ✅ Alle Backend-Funktionen verfügbar
- ⚠️ Keine echte Container-Isolation

## Produktionsumgebung

In der Produktionsumgebung sollten die Header auf Server-Ebene gesetzt werden:

1. **Shared Hosting:** .htaccess wird automatisch verwendet
2. **VPS/Dedicated:** Nginx/Apache-Konfiguration anpassen
3. **Cloud:** Load Balancer/CDN-Konfiguration

## Debugging

Um zu prüfen, ob Cross-Origin-Isolation aktiv ist:

```javascript
console.log('crossOriginIsolated:', crossOriginIsolated);
console.log('SharedArrayBuffer available:', typeof SharedArrayBuffer !== 'undefined');
```

## Aktueller Status

- ✅ Fallback-Modus implementiert
- ✅ Automatische Erkennung von Cross-Origin-Problemen  
- ✅ Benutzerfreundliche Fehlermeldungen
- ✅ Vollständige Chat- und Backend-Funktionalität

Das System funktioniert jetzt sowohl mit als auch ohne WebContainer-Isolation!
