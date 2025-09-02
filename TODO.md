TODO: Vibe Your Course – Prompt-basierte App-Generierung
🎯 Vision: Studierende werden zu App-Architekten durch "Vibe Coding"
Dieses KI-gestützte Moodle-Plugin transformiert das Lernen fundamental. Studierende müssen nicht mehr programmieren können, um zu Entwicklern zu werden. Sie beschreiben ihre App-Ideen in natürlicher Sprache, und die KI agiert als ihr persönlicher Entwickler, der eigene lauffähige "Micro Apps" baut. Der Fokus liegt auf der Idee, dem Konzept und dem kreativen Problemlösen.

🏗 Architektur-Überblick ("Vibe Coding"-Fokus)
Moodle Plugin ◄─► KI APIs (App Generation) ◄─► Browser Runtime
     ↓                       ↓                         ↓
• Prompt Management     • App-Skelette generieren   • Live App Preview
• Lernfortschritt       • Iteratives Verbessern     • App ausführen
• Projekt-Galerie       • Fehler-Analyse            • Kein Code-Schreiben


🎯 Phase 1: Fundament & Plugin-Struktur
✅ Abgeschlossen
[x] Git-Repository und Projekt-Struktur

[x] Vision in README.md und ARCHITECTURE_REVISED.md geschärft

[x] 🏗 Basis Plugin-Struktur

[x] version.php, lib.php, db/access.php

[x] mod_form.php für detaillierte Aktivitätseinstellungen

[x] 🗃 Umfassendes Datenbank-Schema (db/install.xml)

[x] Tabellen für Projekte, Ausführungen, KI-Interaktionen und Lernanalysen

[x] 🎨 UI-Grundgerüst in view.php

[x] Ansicht für "Meine Projekte" und Modal zur Projekterstellung

[x] Layout-Platzhalter für die IDE

🎯 Phase 2: Die "Vibe Coding" Engine (In Arbeit)
[x] 🎨 SPA Frontend Setup (z.B. mit React/Vue) - Bleibt erforderlich

[ ] 🤖 Haupt-Interface: Prompt-Eingabe & KI-Chat - Ersetzt den Code-Editor als primäres Werkzeug

[ ] 🔌 AJAX-Endpunkte (ajax.php) für Prompt-Verarbeitung - Fokus auf Senden von Prompts statt Code

[ ] 🤖 KI-Anbindung für App-Generierung - Das Kernstück: KI muss vollständige Projektdateien (JSON) zurückgeben

[ ] (Optional) 👁️ Code-Ansicht zur Transparenz - Ein einfacher Betrachter (statt CodeMirror) reicht aus, um den generierten Code anzuzeigen.

🎯 Phase 3: App-Ausführung & Sharing (Geplant)
[ ] 🐍 Pyodide Integration zur Ausführung der KI-generierten Python-Apps

[ ] 🌐 WebContainer Integration zur Ausführung der KI-generierten Web-Apps

[ ] 갤러리 Project Gallery

[ ] UI zur Anzeige und zum Durchsuchen der von Studierenden generierten Apps

[ ] Funktion zum "Veröffentlichen" eines Projekts in der Galerie

[ ] 🔄 App Sharing & Feedback

[ ] Ansicht zur Betrachtung und Interaktion mit den Apps anderer

[ ] Basis-Kommentarfunktion für Peer-Feedback

🎯 Phase 4: Lernanalytik & Gamification (Geplant)
[ ] 📈 Lernanalytik-Dashboard (Fokus auf Prompt-Qualität und Problemlöse-Strategien)

[ ] 🏆 Gamification-System (Badges für "Erste App generiert", "Komplexes Feature beschrieben" etc.)

🎯 Nächste unmittelbare Aktionen
🔥 Frontend-Framework (SPA) aufsetzen und in view.php integrieren. (🕕 M)

🔥 Das zentrale Prompt-Interface implementieren (Eingabefeld, Senden-Button, Chat-Verlauf zur Anzeige der Konversation mit der KI). (🕕 M)

⚡ Ersten ajax.php-Endpunkt erstellen, der einen User-Prompt an die KI-API sendet und die Text-Antwort der KI im Chat-Interface anzeigt. (🕕 M)