<?php
/**
 * WebContainer Proxy
 * Leitet WebContainer-URLs weiter und umgeht Moodle-Routing
 */

// Nur in Moodle-Kontext ausführen
if (!defined('MOODLE_INTERNAL')) {
    require_once('../../config.php');
}

// CORS-Header setzen
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// OPTIONS-Request für CORS-Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// WebContainer-URL aus Query-Parameter holen
$webcontainer_url = optional_param('url', '', PARAM_URL);

if (empty($webcontainer_url)) {
    http_response_code(400);
    echo json_encode(['error' => 'Keine WebContainer-URL angegeben']);
    exit;
}

// Prüfe, ob es eine gültige WebContainer-URL ist
if (!preg_match('/webcontainer-api\.io/', $webcontainer_url)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige WebContainer-URL']);
    exit;
}

try {
    // HTTP-Context für die Weiterleitung erstellen
    $context = stream_context_create([
        'http' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; Moodle WebContainer Proxy)',
                'Accept: */*'
            ],
            'timeout' => 30
        ]
    ]);

    // Anfrage an WebContainer weiterleiten
    $response = file_get_contents($webcontainer_url, false, $context);
    
    if ($response === false) {
        throw new Exception('Fehler beim Laden der WebContainer-URL');
    }

    // Content-Type aus HTTP-Headers ermitteln
    $content_type = 'text/html';
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (stripos($header, 'content-type:') === 0) {
                $content_type = trim(substr($header, 13));
                break;
            }
        }
    }

    // Response Headers setzen
    header('Content-Type: ' . $content_type);
    header('Cache-Control: no-cache');
    
    // Response ausgeben
    echo $response;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Proxy-Fehler: ' . $e->getMessage(),
        'url' => $webcontainer_url
    ]);
}
