<?php
class HomeController {
    public function index() {
        // Log para debug
        error_log('HomeController::index chamado');

        // Simular dados para teste
        $data = [
            'success' => true,
            'message' => 'BFF funcionando',
            'controller' => 'HomeController',
            'method' => 'index',
            'server' => [
                'request_uri' => $_SERVER['REQUEST_URI'],
                'request_method' => $_SERVER['REQUEST_METHOD']
            ],
            'session' => [
                'active' => isset($_SESSION['jwt']),
                'jwt_present' => isset($_SESSION['jwt']) ? 'sim' : 'não'
            ]
        ];

        // Para teste: simular um token JWT
        if (!isset($_SESSION['jwt'])) {
            $_SESSION['jwt'] = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...';
        }

        // Retornar resposta em JSON
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}