<?php
// Headers básicos
header('Content-Type: application/json');

// Log da requisição
error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);

// Se for uma requisição OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obter o path da requisição
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
error_log('Parsed path: ' . $path);

// Resposta padrão
$response = [
    'success' => true,
    'message' => 'API funcionando',
    'path' => $path,
    'time' => date('Y-m-d H:i:s'),
    'server' => [
        'request_uri' => $_SERVER['REQUEST_URI'],
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'script_name' => $_SERVER['SCRIPT_NAME']
    ]
];

// Enviar resposta
echo json_encode($response, JSON_PRETTY_PRINT);