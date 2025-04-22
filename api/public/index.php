<?php
// Habilitar exibição de erros em desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Se for uma requisição OPTIONS, retorna 200
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug
error_log("\n=== Nova requisição API ===");
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Método: " . $_SERVER['REQUEST_METHOD']);

// Obtém o path da requisição
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Headers padrão para JSON
header('Content-Type: application/json');

// Roteamento
try {
    switch ($path) {
        case '':
        case 'status':
            echo json_encode([
                'success' => true,
                'version' => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'online',
                'server' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'],
                    'memory_usage' => memory_get_usage(true)
                ]
            ]);
            break;

        case 'health':
        case 'healthcheck':
            echo json_encode([
                'status' => 'UP',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'produtos':
            echo json_encode([
                'success' => true,
                'message' => 'Lista de produtos',
                'data' => [
                    ['id' => 1, 'nome' => 'Produto 1', 'preco' => 10.00],
                    ['id' => 2, 'nome' => 'Produto 2', 'preco' => 20.00],
                    ['id' => 3, 'nome' => 'Produto 3', 'preco' => 30.00]
                ]
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'error' => true,
                'message' => 'Rota não encontrada',
                'path' => $path
            ]);
            break;
    }
} catch (Exception $e) {
    error_log("Erro na API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Erro interno do servidor',
        'debug' => $e->getMessage()
    ]);
}