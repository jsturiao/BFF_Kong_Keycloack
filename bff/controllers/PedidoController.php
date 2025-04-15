<?php
class PedidoController {
    private string $apiUrl = 'http://kong:8000/api';

    public function listar() {
        if (!isset($_SESSION['jwt'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Usuário não autenticado']);
            return;
        }

        // Simular dados de pedidos para esta fase
        // Na próxima fase, isso virá da API
        $dados = [
            'success' => true,
            'data' => [
                'pedidos' => [
                    [
                        'id' => 1,
                        'cliente' => 'Cliente 1',
                        'total' => 299.90,
                        'status' => 'Em processamento'
                    ],
                    [
                        'id' => 2,
                        'cliente' => 'Cliente 2',
                        'total' => 499.90,
                        'status' => 'Enviado'
                    ]
                ]
            ]
        ];

        include '/var/www/views/pedidos.php';
    }
}