<?php
// Debug
error_log('Processando rota: ' . $_SERVER['REQUEST_URI']);

// Obter o path da requisição
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Log do URI processado
error_log('URI processado: ' . $uri);

try {
    // Roteamento
    switch ($uri) {
        case '/':
        case '/inicio':
            require_once '/var/www/controllers/HomeController.php';
            (new HomeController())->index();
            break;

        case '/produtos':
            require_once '/var/www/controllers/ProdutoController.php';
            (new ProdutoController())->listar();
            break;

        case '/usuarios':
            require_once '/var/www/controllers/UsuarioController.php';
            (new UsuarioController())->listar();
            break;

        case '/pedidos':
            require_once '/var/www/controllers/PedidoController.php';
            (new PedidoController())->listar();
            break;

        case '/logout':
            session_destroy();
            header('Location: /');
            exit;
            break;

        default:
            // Log de rota não encontrada
            error_log('Rota não encontrada: ' . $uri);
            
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'error' => true,
                'message' => 'Página não encontrada',
                'path' => $uri
            ]);
            break;
    }
} catch (Exception $e) {
    error_log('Erro no roteamento: ' . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Erro interno do servidor',
        'debug' => [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}