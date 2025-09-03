<?php
// Schneller Header-Test für WebContainer

// Set Cross-Origin-Isolation headers
header('Cross-Origin-Embedder-Policy: require-corp');
header('Cross-Origin-Opener-Policy: same-origin');
header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html>
<html>
<head>
    <title>WebContainer Header Test</title>
    <meta charset="utf-8">
</head>
<body>
    <h1>🧪 WebContainer Header Test</h1>
    
    <div id="test-results"></div>
    
    <script>
        const results = document.getElementById('test-results');
        
        function addResult(test, passed, info = '') {
            const status = passed ? '✅' : '❌';
            const color = passed ? 'green' : 'red';
            results.innerHTML += `<p style="color: ${color}"><strong>${status} ${test}</strong> ${info}</p>`;
        }
        
        // Debug: Alle verfügbaren APIs
        results.innerHTML += '<h3>🔍 Debug-Informationen:</h3>';
        results.innerHTML += `<p><strong>User Agent:</strong> ${navigator.userAgent}</p>`;
        results.innerHTML += `<p><strong>URL:</strong> ${window.location.href}</p>`;
        results.innerHTML += `<p><strong>Protocol:</strong> ${window.location.protocol}</p>`;
        results.innerHTML += `<p><strong>Port:</strong> ${window.location.port}</p>`;
        
        // Test 1: SharedArrayBuffer
        const hasSharedArrayBuffer = typeof SharedArrayBuffer !== 'undefined';
        addResult('SharedArrayBuffer verfügbar', hasSharedArrayBuffer, hasSharedArrayBuffer ? 'API gefunden' : 'API nicht verfügbar');
        
        // Test 2: crossOriginIsolated
        const isCrossOriginIsolated = crossOriginIsolated === true;
        addResult('crossOriginIsolated aktiv', isCrossOriginIsolated, isCrossOriginIsolated ? 'true' : 'false');
        
        // Test 3: Headers in Response
        fetch(window.location.href, { cache: 'no-store' })
            .then(response => {
                const coep = response.headers.get('Cross-Origin-Embedder-Policy');
                const coop = response.headers.get('Cross-Origin-Opener-Policy');
                
                addResult('COEP Header gesetzt', !!coep, coep ? `(${coep})` : '');
                addResult('COOP Header gesetzt', !!coop, coop ? `(${coop})` : '');
                
                // Zusätzliche Header-Informationen
                results.innerHTML += '<h3>📋 Alle Response-Header:</h3>';
                response.headers.forEach((value, key) => {
                    results.innerHTML += `<p><strong>${key}:</strong> ${value}</p>`;
                });
                
                // Gesamtergebnis
                const allPassed = hasSharedArrayBuffer && isCrossOriginIsolated && coep && coop;
                
                results.innerHTML += '<hr>';
                if (allPassed) {
                    results.innerHTML += '<h2 style="color: green">🎉 WebContainer bereit!</h2>';
                    results.innerHTML += '<p>Alle Tests bestanden. WebContainer sollte vollständig funktionieren.</p>';
                } else {
                    results.innerHTML += '<h2 style="color: red">⚠️ WebContainer wird im Fallback-Modus laufen</h2>';
                    results.innerHTML += '<p>Einige Tests fehlgeschlagen. Prüfe die obigen Punkte.</p>';
                }
            })
            .catch(error => {
                addResult('Header-Abruf', false, error.message);
            });
    </script>
</body>
</html>
