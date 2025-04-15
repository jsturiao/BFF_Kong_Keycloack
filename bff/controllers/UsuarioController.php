<?php
class UsuarioController {
    private string $apiUrl = 'http://kong:8000/api';

    public function listar() {
        if (!isset($_SESSION['jwt'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Usuário não autenticado']);
            return;
        }

        // Simular dados de usuários para esta fase
        // Na próxima fase, isso virá da API
        $dados = [
            'success' => true,
            'data' => [
                'usuarios' => [
                    ['id' => 1, 'nome' => 'Admin', 'email' => 'admin@example.com'],
                    ['id' => 2, 'nome' => 'Usuário', 'email' => 'user@example.com']
                ]
            ]
        ];

        include '/var/www/views/usuarios.php';
    }
}