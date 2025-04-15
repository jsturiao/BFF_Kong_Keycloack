<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug
$debug = [
    'request_uri' => $_SERVER['REQUEST_URI'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME']
];
error_log('Requisição recebida: ' . json_encode($debug));

try {
    // Definir diretório base
    define('BASE_DIR', '/var/www');
    
    // Obter URI da requisição e remover qualquer vírgula extra (fix para o problema observado)
    $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ',');
    
    // Log da rota
    error_log('Processando rota: ' . $uri);
    
    // Roteamento básico
    switch ($uri) {
        case '/':
            require_once BASE_DIR . '/controllers/HomeController.php';
            $controller = new HomeController();
            $controller->index();
            break;
            
        case '/produtos':
            error_log('Carregando ProdutoController');
            require_once BASE_DIR . '/controllers/ProdutoController.php';
            $controller = new ProdutoController();
            $controller->listar();
            break;
            
        default:
            error_log('Rota não encontrada: ' . $uri);
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'error' => true,
                'message' => 'Rota não encontrada',
                'path' => $uri
            ]);
            break;
    }

} catch (Throwable $e) {
    error_log('Erro no processamento: ' . $e->getMessage());
    $debug['error'] = $e->getMessage();
    $debug['file'] = $e->getFile();
    $debug['line'] = $e->getLine();
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Erro ao processar requisição',
        'debug' => $debug
    ]);
}