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

🎯 Phase 2: Die "Vibe Coding" Engine ✅ ABGESCHLOSSEN
[x] 🎨 SPA Frontend Setup (z.B. mit React/Vue) - Abgeschlossen

[x] 🤖 Haupt-Interface: Prompt-Eingabe & KI-Chat - Chat-Interface implementiert mit 3 Tabs

[x] 🔌 AJAX-Endpunkte (ajax.php) für Prompt-Verarbeitung - process_prompt Endpunkt funktioniert

[ ] 🤖 KI-Anbindung für App-Generierung - Das Kernstück: KI muss vollständige Projektdateien (JSON) zurückgeben (TODO: Claude-API)

[ ] (Optional) 👁️ Code-Ansicht zur Transparenz - Ein einfacher Betrachter (statt CodeMirror) reicht aus, um den generierten Code anzuzeigen.

🎯 Phase 3: App-Ausführung & WebContainer ✅ ABGESCHLOSSEN
[x] 🌐 WebContainer Integration zur Ausführung der KI-generierten Web-Apps (Browser-API korrekt implementiert!)

[x] 🔧 WebContainer-Output im Frontend sichtbar machen (Console-Tab zeigt Output)

[x] 🔧 Cross-Origin-Isolation Headers implementiert für SharedArrayBuffer Support

[x] 🧹 Überflüssige Docker-Setup-Skripte entfernt (WebContainer ist Browser-API, kein Docker!)

[ ] 갤러리 Project Gallery

[ ] UI zur Anzeige und zum Durchsuchen der von Studierenden generierten Apps

[ ] Funktion zum "Veröffentlichen" eines Projekts in der Galerie

[ ] 🔄 App Sharing & Feedback

[ ] Ansicht zur Betrachtung und Interaktion mit den Apps anderer

[ ] Basis-Kommentarfunktion für Peer-Feedback

🎯 Phase 4: Pyodide Integration (Später)
[ ] 🐍 Pyodide Integration zur Ausführung der KI-generierten Python-Apps (VERSCHOBEN - Fokus liegt erstmal auf WebContainer)

🎯 Phase 5: Lernanalytik & Gamification (Geplant)
[ ] 📈 Lernanalytik-Dashboard (Fokus auf Prompt-Qualität und Problemlöse-Strategien)

[ ] 🏆 Gamification-System (Badges für "Erste App generiert", "Komplexes Feature beschrieben" etc.)

🎯 Aktuelle Funktionalität ✅ FUNKTIONIERT!
✅ Frontend-Framework (SPA) aufsetzen und in view.php integrieren. (Abgeschlossen)

✅ Chat-Interface implementieren - Eingabefeld, Senden-Button, Chat-Verlauf für Konversation mit KI (Abgeschlossen)

✅ Pyodide-Option aus Frontend-Auswahl entfernen (vorerst nicht verfügbar) (Abgeschlossen)

✅ AJAX-Endpunkt für Prompt-Verarbeitung erstellen (Abgeschlossen - Datenbank-Fehler behoben)

✅ WebContainer-Integration für Hello World-Anzeige implementieren (Abgeschlossen - Automatischer Start bei IDE-Öffnung)

✅ WebContainer-Output im Frontend sichtbar machen (Abgeschlossen - Console-Tab funktioniert)

🎯 Nächste Schritte (Priorität)
🔥 Claude-API Integration für intelligente App-Generierung (🕕 L)

🔥 Erweiterte WebContainer-Apps basierend auf Prompts generieren (🕕 L)

⚡ Template-System für verschiedene App-Typen (🕕 M)