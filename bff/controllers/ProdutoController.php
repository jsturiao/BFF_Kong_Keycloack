<?php
class ProdutoController {
    private string $version = '2025.04.22.5';
    
    private array $services = [
        'kong' => [
            'url' => 'http://kong:8000/api/produtos',
            'tried' => false,
            'status' => null,
            'error' => null
        ],
        'api' => [
            'url' => 'http://api:8080/produtos',
            'tried' => false,
            'status' => null,
            'error' => null
        ]
    ];

    public function listar() {
        error_log("=== ProdutoController::listar v{$this->version} iniciado ===");
        
        // Tentar vários métodos para obter o header Authorization
        $authHeader = $this->getAuthorizationHeader();
        error_log('Authorization Header encontrado: ' . ($authHeader ?: 'não'));

        if (!$authHeader) {
            error_log('Token não encontrado em nenhum método');
            return $this->sendErrorResponse(401, 'Token não encontrado', [
                'server_vars' => array_filter($_SERVER, function($key) {
                    return strpos($key, 'HTTP_') === 0 || strpos($key, 'AUTH') !== false;
                }, ARRAY_FILTER_USE_KEY)
            ]);
        }

        // Extrair o token
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            error_log('Token mal formatado: ' . $authHeader);
            return $this->sendErrorResponse(401, 'Token inválido');
        }

        $token = $matches[1];
        $_SESSION['jwt'] = $token;
        error_log('Token extraído: ' . substr($token, 0, 50) . '...');

        // 1. Tentar Kong
        error_log('Tentando Kong...');
        $kongResponse = $this->tryService('kong', [
            "Authorization: Bearer $token"
        ]);
        
        if ($kongResponse !== false) {
            error_log('Kong respondeu com sucesso!');
            return $this->sendServiceResponse($kongResponse, 'kong');
        }

        // 2. Tentar API direta como fallback
        error_log('Kong falhou, tentando API direta...');
        $apiResponse = $this->tryService('api', [
            "Authorization: Bearer $token"
        ]);
        
        if ($apiResponse !== false) {
            error_log('API respondeu com sucesso!');
            return $this->sendServiceResponse($apiResponse, 'api');
        }

        // 3. Mock como último recurso
        error_log('Todas as tentativas falharam, usando mock');
        return $this->sendMockResponse();
    }

    private function getAuthorizationHeader(): ?string {
        $methods = [
            // Apache headers
            function() {
                if (function_exists('apache_request_headers')) {
                    $headers = apache_request_headers();
                    if (isset($headers['Authorization'])) {
                        error_log('Token encontrado via apache_request_headers');
                        return $headers['Authorization'];
                    }
                }
                return null;
            },
            // $_SERVER diretamente
            function() {
                if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                    error_log('Token encontrado via $_SERVER[HTTP_AUTHORIZATION]');
                    return $_SERVER['HTTP_AUTHORIZATION'];
                }
                return null;
            },
            // REDIRECT_HTTP_AUTHORIZATION
            function() {
                if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                    error_log('Token encontrado via REDIRECT_HTTP_AUTHORIZATION');
                    return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                }
                return null;
            }
        ];

        foreach ($methods as $method) {
            $result = $method();
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private function tryService(string $service, array $headers = []): string|false {
        if (!isset($this->services[$service])) {
            return false;
        }

        $config = &$this->services[$service];
        error_log("Requisição para {$service}: {$config['url']}");
        error_log("Headers: " . json_encode($headers));

        $ch = curl_init($config['url']);
        
        $allHeaders = array_merge(
            ['Content-Type: application/json', 'Accept: application/json'],
            $headers
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => $allHeaders,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        $endTime = microtime(true);

        $config['tried'] = true;
        $config['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $config['error'] = curl_error($ch) ?: null;
        $config['time'] = round(($endTime - $startTime) * 1000, 2);
        
        curl_close($ch);

        error_log(sprintf(
            'Resposta %s: HTTP %d, %.2fms, Erro: %s, Response: %s',
            $service,
            $config['status'],
            $config['time'],
            $config['error'] ?: 'Nenhum',
            substr($response ?: '', 0, 100)
        ));

        if ($response === false || $config['error']) {
            error_log("Erro na requisição para $service: " . ($config['error'] ?: 'Erro desconhecido'));
            return false;
        }

        if ($config['status'] === 401) {
            error_log("Erro de autenticação no $service");
            return false;
        }

        return $config['status'] === 200 ? $response : false;
    }

    private function sendServiceResponse(string $response, string $via): void {
        $data = json_decode($response, true);
        if (!is_array($data)) {
            $this->sendErrorResponse(500, 'Erro ao processar resposta');
            return;
        }

        $data['debug'] = [
            'version' => $this->version,
            'via' => $via,
            'timestamp' => date('Y-m-d H:i:s'),
            'auth' => [
                'jwt_present' => isset($_SESSION['jwt']),
                'jwt_length' => isset($_SESSION['jwt']) ? strlen($_SESSION['jwt']) : 0
            ],
            'services' => array_map(fn($s) => [
                'url' => $s['url'],
                'status' => $s['status'],
                'tried' => $s['tried'],
                'error' => $s['error'],
                'time' => $s['time'] ?? null
            ], $this->services)
        ];

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    private function sendMockResponse(): void {
        $response = [
            'success' => true,
            'source' => 'mock',
            'version' => $this->version,
            'data' => [
                'produtos' => [
                    ['id' => 1, 'nome' => 'Mock 1', 'preco' => 100.00],
                    ['id' => 2, 'nome' => 'Mock 2', 'preco' => 200.00]
                ]
            ],
            'debug' => [
                'version' => $this->version,
                'via' => 'mock',
                'timestamp' => date('Y-m-d H:i:s'),
                'auth' => [
                    'jwt_present' => isset($_SESSION['jwt']),
                    'jwt_length' => isset($_SESSION['jwt']) ? strlen($_SESSION['jwt']) : 0
                ],
                'services' => array_map(fn($s) => [
                    'url' => $s['url'],
                    'status' => $s['status'],
                    'tried' => $s['tried'],
                    'error' => $s['error'],
                    'time' => $s['time'] ?? null
                ], $this->services)
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    private function sendErrorResponse(int $status, string $message, array $extra = []): void {
        http_response_code($status);
        header('Content-Type: application/json');
        
        $debug = [
            'version' => $this->version,
            'timestamp' => date('Y-m-d H:i:s'),
            'auth' => [
                'jwt_present' => isset($_SESSION['jwt']),
                'jwt_length' => isset($_SESSION['jwt']) ? strlen($_SESSION['jwt']) : 0,
                'auth_header' => $this->getAuthorizationHeader()
            ]
        ];

        if (!empty($extra)) {
            $debug = array_merge($debug, $extra);
        }

        echo json_encode([
            'error' => true,
            'message' => $message,
            'debug' => $debug
        ], JSON_PRETTY_PRINT);
    }
}