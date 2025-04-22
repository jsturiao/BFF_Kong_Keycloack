<?php
// Habilitar exibição de erros em desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega classes necessárias
require_once __DIR__ . '/../controllers/HeaderHelper.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

// Debug inicial
error_log("\n=== Nova requisição BFF ===");
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Método: " . $_SERVER['REQUEST_METHOD']);

// Obtém o path da requisição
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? $_SERVER['REQUEST_URI'], '/');
error_log("Path processado: " . $path);

// Rotas HTML não devem ter header JSON
$htmlRoutes = ['', 'dashboard'];
$isHtmlRoute = in_array($path, $htmlRoutes);

// Se for uma requisição OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    HeaderHelper::setCorsHeaders();
    http_response_code(200);
    exit();
}

// Roteamento
try {
    $controller = new DashboardController();
    
    switch ($path) {
        case '':
        case 'dashboard':
            error_log("Renderizando dashboard");
            $controller->index();
            break;

        case 'status':
            error_log("Obtendo status");
            $controller->status();
            break;

        case 'produtos':
            HeaderHelper::setJsonHeaders();
            echo json_encode([
                'success' => true,
                'message' => 'Rota de produtos',
                'method' => $_SERVER['REQUEST_METHOD']
            ]);
            break;

        default:
            error_log("Rota não encontrada: " . $path);
            http_response_code(404);
            
            if ($isHtmlRoute) {
                HeaderHelper::setHtmlHeaders();
                echo "Página não encontrada";
            } else {
                HeaderHelper::setJsonHeaders();
                echo json_encode([
                    'error' => true,
                    'message' => 'Rota não encontrada',
                    'path' => '/' . $path
                ]);
            }
            break;
    }
} catch (Exception $e) {
    error_log("Erro no BFF: " . $e->getMessage());
    http_response_code(500);
    
    if ($isHtmlRoute) {
        HeaderHelper::setHtmlHeaders();
        echo "Erro interno do servidor: " . $e->getMessage();
    } else {
        HeaderHelper::setJsonHeaders();
        echo json_encode([
            'error' => true,
            'message' => 'Erro interno do servidor',
            'debug' => $e->getMessage()
        ]);
    }
}