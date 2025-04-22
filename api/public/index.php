<?php
// Headers básicos
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Debug inicial
error_log("\n=== Nova requisição API ===");
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'não definido'));
error_log("PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'não definido'));

// Lista de produtos mock
$produtos = [
    [
        'id' => 1,
        'nome' => 'Produto API 1',
        'preco' => 100.00,
        'descricao' => 'Produto 1 da API'
    ],
    [
        'id' => 2,
        'nome' => 'Produto API 2',
        'preco' => 200.00,
        'descricao' => 'Produto 2 da API'
    ],
    [
        'id' => 3,
        'nome' => 'Produto API 3',
        'preco' => 300.00,
        'descricao' => 'Produto 3 da API'
    ]
];

// Se for uma requisição OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Função para enviar resposta JSON
function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Função para verificar autenticação
function checkAuth() {
    $headers = getallheaders();
    error_log("Headers da requisição: " . json_encode($headers));

    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$authHeader) {
        error_log("Token não encontrado");
        return false;
    }

    error_log("Token encontrado: " . substr($authHeader, 0, 50) . "...");
    return true;
}

// Obter path da requisição
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($uri, '/');
error_log("Path processado: '$path'");

// Roteamento
try {
    if ($path === 'produtos') {
        error_log("Acessando rota /produtos");

        if (!checkAuth()) {
            sendResponse(401, [
                'error' => true,
                'message' => 'Não autorizado',
                'debug' => [
                    'headers' => getallheaders()
                ]
            ]);
        }

        sendResponse(200, [
            'success' => true,
            'source' => 'API Direta',
            'data' => [
                'produtos' => $produtos
            ],
            'debug' => [
                'path' => $path,
                'method' => $_SERVER['REQUEST_METHOD'],
                'headers' => getallheaders()
            ]
        ]);
    } 
    else if ($path === '' || $path === 'status') {
        sendResponse(200, [
            'success' => true,
            'message' => 'API em execução',
            'version' => '1.0.0',
            'time' => date('Y-m-d H:i:s')
        ]);
    } 
    else {
        error_log("Rota não encontrada: $path");
        sendResponse(404, [
            'error' => true,
            'message' => 'Rota não encontrada',
            'path' => $path
        ]);
    }
} catch (Exception $e) {
    error_log("Erro na API: " . $e->getMessage());
    sendResponse(500, [
        'error' => true,
        'message' => 'Erro interno do servidor',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}