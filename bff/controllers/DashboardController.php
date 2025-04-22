<?php
require_once __DIR__ . '/HeaderHelper.php';

class DashboardController {
    private $startTime;

    private $config = [
        'kong' => [
            'host' => 'http://kong:8001',
            'version' => '3.4.2'  // Kong usa esta versão
        ],
        'keycloak' => [
            'internal_url' => 'http://keycloak:8080',
            'external_port' => 8082,
            'version' => '23.0.3'  // Keycloak usa esta versão
        ],
        'api' => [
            'host' => 'http://api',
            'external_port' => 8081
        ]
    ];

    public function __construct() {
        $this->startTime = microtime(true);
    }

    public function index() {
        error_log("DashboardController::index()");
        HeaderHelper::setHtmlHeaders();
        
        $viewPath = __DIR__ . '/../views/dashboard.php';
        if (!file_exists($viewPath)) {
            throw new Exception("View não encontrada");
        }
        
        require_once $viewPath;
    }

    public function status() {
        error_log("DashboardController::status()");
        
        try {
            HeaderHelper::setJsonHeaders();
            
            $services = [
                'bff' => $this->checkBFF(),
                'kong' => $this->checkKong(),
                'keycloak' => $this->checkKeycloak(),
                'api' => $this->checkAPI()
            ];

            foreach ($services as $name => $status) {
                error_log(sprintf(
                    "Status do serviço %s: %s (Resposta em %s ms)",
                    $name,
                    $status['status'] ? 'ONLINE' : 'OFFLINE',
                    $status['responseTime'] ?? 'N/A'
                ));
            }

            echo json_encode([
                'success' => true,
                'version' => '2025.04.22',
                'services' => $services,
                'responseTime' => round((microtime(true) - $this->startTime) * 1000, 2),
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            error_log("Erro ao verificar status: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Erro ao verificar status dos serviços',
                'debug' => $e->getMessage()
            ]);
        }
    }

    private function checkBFF() {
        return [
            'status' => true,
            'port' => 8080,
            'version' => '2025.04.22',
            'responseTime' => round((microtime(true) - $this->startTime) * 1000, 2)
        ];
    }

    private function checkKong() {
        $start = microtime(true);
        try {
            $response = $this->makeRequest($this->config['kong']['host'], timeout: 2);
            $data = json_decode($response, true);
            return [
                'status' => true,
                'admin_port' => 8001,
                'proxy_port' => 8000,
                'version' => $data['version'] ?? $this->config['kong']['version'],
                'responseTime' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (Exception $e) {
            error_log("Erro ao verificar Kong: " . $e->getMessage());
            return [
                'status' => false,
                'admin_port' => 8001,
                'proxy_port' => 8000,
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkKeycloak() {
        $start = microtime(true);
        try {
            $baseUrl = rtrim($this->config['keycloak']['internal_url'], '/');
            $url = $baseUrl . '/realms/master';
            error_log("Verificando Keycloak em: " . $url);
            
            $response = $this->makeRequest($url, timeout: 2);
            
            return [
                'status' => true,
                'port' => $this->config['keycloak']['external_port'],
                'version' => $this->config['keycloak']['version'],
                'responseTime' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (Exception $e) {
            error_log("Erro ao verificar Keycloak: " . $e->getMessage());
            return [
                'status' => false,
                'port' => $this->config['keycloak']['external_port'],
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkAPI() {
        $start = microtime(true);
        try {
            $url = rtrim($this->config['api']['host'], '/') . '/status';
            $response = $this->makeRequest($url, timeout: 2);
            $data = json_decode($response, true);
            return [
                'status' => true,
                'port' => $this->config['api']['external_port'],
                'version' => $data['version'] ?? 'unknown',
                'responseTime' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (Exception $e) {
            error_log("Erro ao verificar API: " . $e->getMessage());
            return [
                'status' => false,
                'port' => $this->config['api']['external_port'],
                'error' => $e->getMessage()
            ];
        }
    }

    private function makeRequest($url, $timeout = 5) {
        error_log("Fazendo requisição para: " . $url);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FAILONERROR => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($error) {
            error_log("Erro na requisição para $url: $error");
            throw new Exception($error);
        }

        if ($info['http_code'] >= 400) {
            error_log("HTTP {$info['http_code']} ao acessar $url");
            throw new Exception("HTTP {$info['http_code']}");
        }

        return $response;
    }
}