<?php
class ProdutoController {
    private string $version = '2025.04.15.2';
    
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
        
        // 1. Tentar Kong
        error_log('Tentando via Kong...');
        $kongResponse = $this->tryService('kong');
        if ($kongResponse !== false) {
            error_log('Kong respondeu com sucesso!');
            return $this->sendServiceResponse($kongResponse, 'kong');
        }

        // 2. Tentar API direta
        error_log('Tentando via API direta...');
        $apiResponse = $this->tryService('api');
        if ($apiResponse !== false) {
            error_log('API respondeu com sucesso!');
            return $this->sendServiceResponse($apiResponse, 'api');
        }

        // 3. Usar mock como último recurso
        error_log('Usando dados mock após falhas nas tentativas');
        return $this->sendMockResponse();
    }

    private function tryService(string $service): string|false {
        if (!isset($this->services[$service])) {
            error_log("Serviço desconhecido: $service");
            return false;
        }

        $config = &$this->services[$service];
        error_log("Tentando acessar {$service}: {$config['url']}");

        $ch = curl_init($config['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        $endTime = microtime(true);

        $config['tried'] = true;
        $config['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $config['error'] = curl_error($ch) ?: null;
        $config['time'] = round(($endTime - $startTime) * 1000, 2); // tempo em ms
        
        curl_close($ch);

        error_log(sprintf(
            'Resposta de %s: HTTP %d, Tempo: %.2fms, Erro: %s',
            $service,
            $config['status'],
            $config['time'],
            $config['error'] ?: 'Nenhum'
        ));

        return $config['status'] === 200 ? $response : false;
    }

    private function sendServiceResponse(string $response, string $via): void {
        $responseData = json_decode($response, true);
        if (!is_array($responseData)) {
            error_log("Erro ao decodificar resposta JSON de $via");
            $this->sendMockResponse();
            return;
        }

        $responseData['debug'] = [
            'version' => $this->version,
            'via' => $via,
            'timestamp' => date('Y-m-d H:i:s'),
            'services' => array_map(function($service) {
                return [
                    'url' => $service['url'],
                    'status' => $service['status'],
                    'tried' => $service['tried'],
                    'error' => $service['error'],
                    'time' => $service['time'] ?? null
                ];
            }, $this->services)
        ];

        header('Content-Type: application/json');
        echo json_encode($responseData, JSON_PRETTY_PRINT);
    }

    private function sendMockResponse(): void {
        $response = [
            'success' => true,
            'source' => 'mock',
            'version' => $this->version,
            'data' => [
                'produtos' => [
                    ['id' => 1, 'nome' => 'Mock Produto 1', 'preco' => 100.00],
                    ['id' => 2, 'nome' => 'Mock Produto 2', 'preco' => 200.00]
                ]
            ],
            'debug' => [
                'version' => $this->version,
                'via' => 'mock',
                'timestamp' => date('Y-m-d H:i:s'),
                'services' => array_map(function($service) {
                    return [
                        'url' => $service['url'],
                        'status' => $service['status'],
                        'tried' => $service['tried'],
                        'error' => $service['error'],
                        'time' => $service['time'] ?? null
                    ];
                }, $this->services)
            ]
        ];

        error_log('Enviando resposta mock: ' . json_encode($response));
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}