<?php
// Simple test AJAX endpoint
define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple test response
echo json_encode([
    'success' => true,
    'message' => 'AJAX endpoint is working!',
    'timestamp' => time(),
    'method' => $_SERVER['REQUEST_METHOD'],
    'data' => $_REQUEST
]);
