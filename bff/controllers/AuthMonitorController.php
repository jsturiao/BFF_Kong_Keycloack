<?php
class AuthMonitorController {
    private $requestId;
    private $startTime;

    private $config = [
        'keycloak' => [
            'host' => 'keycloak',
            'port' => '8080',
            'realm' => 'app-demo',
            'public_key' => '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnzyis1ZjfNB0bBgKFMSv\nvkTtwlvBsaJq7S5wA+kzeVOVpVWwkWdVha4s38XM/pa/yr47av7+z3VTmvDRyAHc\naT92whREFpLv9cj5lTeJSibyr/Mrm/YtjCZVWgaOYIhwrXwKLqPr/11inWsAkfIy\ntvHWTxZYEcXLgAXFuUuaS3uF9gEiNQwzGTU1v0FqkqTBr4B8nW3HCN47XUu0t8Y0\ne+lf4s4OxQawWD79J9/5d3Ry0vbV3Am1FtGJiJvOwRsIfVChDpYStTcHTCMqtvWb\nV6L11BWkpzGXSW4Hv43qa+GSYOD2QU68Mb59oSk2OB+BtOLpJofmbGEGgvmwyCI9\nMwIDAQAB\n-----END PUBLIC KEY-----'
        ]
    ];

    public function __construct() {
        $this->requestId = uniqid('auth_', true);
        $this->startTime = microtime(true);
    }

    public function index() {
        error_log("AuthMonitorController::index()");
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/../views/auth.php';
    }

    public function startAuth() {
        error_log("AuthMonitorController::startAuth() - RequestID: " . $this->requestId);
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $flowDetails = $this->validateAndPrepareFlow($input);
            
            // Log do início do fluxo
            $this->logAuthStep('client', 'request', [
                'flow_type' => $flowDetails['grant_type'],
                'client_id' => $flowDetails['client_id'],
                'request_id' => $this->requestId,
                'timestamp' => $this->getTimestamp(),
                'headers' => $this->getRequestHeaders()
            ]);

            // Processamento do BFF
            $bffResponse = $this->processBFFStep($flowDetails);

            // Processamento do Kong
            $kongResponse = $this->processKongStep($bffResponse);

            // Processamento do Keycloak
            $keycloakResponse = $this->processKeycloakStep($kongResponse);

            // Validação do Kong
            $kongValidation = $this->processKongValidation($keycloakResponse);

            // Finalização do BFF
            $bffFinal = $this->processBFFFinalization($kongValidation);

            echo json_encode([
                'success' => true,
                'request_id' => $this->requestId,
                'flow_details' => [
                    'type' => $flowDetails['grant_type'],
                    'steps' => [
                        'client' => $flowDetails,
                        'bff' => $bffResponse,
                        'kong' => $kongResponse,
                        'keycloak' => $keycloakResponse,
                        'kong_validation' => $kongValidation,
                        'bff_final' => $bffFinal
                    ]
                ],
                'timestamp' => $this->getTimestamp()
            ]);

        } catch (Exception $e) {
            $this->logAuthStep('error', 'error', [
                'message' => $e->getMessage(),
                'request_id' => $this->requestId,
                'stack_trace' => $e->getTraceAsString()
            ]);

            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
                'request_id' => $this->requestId,
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }

    private function validateAndPrepareFlow($input) {
        return [
            'grant_type' => $input['flowType'] ?? 'password',
            'client_id' => $input['clientId'] ?? null,
            'username' => $input['username'] ?? null,
            'password' => $input['password'] ?? null,
            'timestamp' => $this->getTimestamp()
        ];
    }

    private function processBFFStep($flowDetails) {
        $this->logAuthStep('bff', 'processing', [
            'message' => 'Preparando requisição para Kong',
            'headers' => $this->getRequestHeaders()
        ]);

        return [
            'flow_id' => uniqid('flow_'),
            'original_request' => $flowDetails,
            'prepared_headers' => [
                'X-Request-ID' => $this->requestId,
                'X-Flow-Type' => $flowDetails['grant_type'],
                'X-Client-IP' => $_SERVER['REMOTE_ADDR'],
                'X-Forwarded-For' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null
            ],
            'processing_time' => $this->getElapsedTime(),
            'memory_usage' => memory_get_usage(true),
            'timestamp' => $this->getTimestamp()
        ];
    }

    private function processKongStep($bffResponse) {
        return [
            'request_id' => $this->requestId,
            'route_matched' => 'auth_route',
            'upstream_url' => 'http://keycloak:8080/auth',
            'applied_plugins' => [
                'cors',
                'key-auth',
                'rate-limiting'
            ],
            'headers_added' => [
                'X-Kong-Route' => 'auth_route',
                'X-Kong-Request-ID' => uniqid('kong_')
            ],
            'rate_limit_details' => [
                'limit' => 5,
                'window_size' => 60,
                'remaining' => 4
            ],
            'ip_restrictions' => [
                'allowed_ips' => ['*'],
                'denied_ips' => []
            ],
            'cors_config' => [
                'origins' => ['*'],
                'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'headers' => ['Content-Type', 'Authorization']
            ],
            'latency' => round(microtime(true) - $this->startTime, 3),
            'upstream_latency' => 0.045,
            'timestamp' => $this->getTimestamp()
        ];
    }

    private function processKeycloakStep($kongResponse) {
        $keyPair = $this->generateKeyPair();
        $tokens = $this->generateTokens($keyPair);

        return array_merge($tokens, [
            'token_details' => [
                'algorithm' => 'RS256',
                'key_id' => uniqid('key_'),
                'public_key' => $this->config['keycloak']['public_key'],
                'key_usage' => 'sig',
                'key_ops' => ['verify'],
                'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
            ],
            'response_headers' => [
                'Cache-Control' => 'no-store',
                'Pragma' => 'no-cache',
                'Content-Type' => 'application/json'
            ],
            'timestamp' => $this->getTimestamp()
        ]);
    }

    private function processKongValidation($keycloakResponse) {
        return [
            'tokens_valid' => true,
            'signature_valid' => true,
            'expiration_valid' => true,
            'issuer_valid' => true,
            'rate_limit_remaining' => 999,
            'cache_status' => 'MISS',
            'cache_key' => hash('sha256', $this->requestId),
            'cache_ttl' => 300,
            'cache_hits' => 0,
            'rate_limit_reset' => time() + 3600,
            'validation_timestamp' => $this->getTimestamp()
        ];
    }

    private function processBFFFinalization($kongValidation) {
        return [
            'request_completed' => true,
            'request_id' => $this->requestId,
            'total_time' => round(microtime(true) - $this->startTime, 3),
            'memory_peak' => memory_get_peak_usage(true),
            'api_calls' => 3,
            'response_size' => rand(2000, 5000),
            'compression_ratio' => 0.7,
            'cache_headers' => [
                'Cache-Control' => 'no-store',
                'Pragma' => 'no-cache'
            ],
            'timestamp' => $this->getTimestamp()
        ];
    }

    private function generateKeyPair() {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res)['key'];

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    private function generateTokens($keyPair) {
        $now = time();
        $exp = $now + 300; // 5 minutos

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => uniqid('key_')
        ];

        $basePayload = [
            'iss' => 'http://keycloak:8080/auth/realms/app-demo',
            'aud' => 'app-demo',
            'exp' => $exp,
            'iat' => $now,
            'jti' => uniqid('jwt_')
        ];

        return [
            'access_token' => $this->generateToken($header, array_merge($basePayload, ['type' => 'access']), $keyPair),
            'refresh_token' => $this->generateToken($header, array_merge($basePayload, ['type' => 'refresh']), $keyPair),
            'id_token' => $this->generateToken($header, array_merge($basePayload, ['type' => 'id']), $keyPair),
            'token_type' => 'Bearer',
            'expires_in' => 300,
            'session_state' => uniqid('session_'),
            'scope' => 'openid profile email'
        ];
    }

    private function generateToken($header, $payload, $keyPair) {
        $header = base64_encode(json_encode($header));
        $payload = base64_encode(json_encode($payload));
        $signature = base64_encode(uniqid('sig_')); // Simulação
        return "$header.$payload.$signature";
    }

    private function getRequestHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    private function getTimestamp() {
        return date('Y-m-d H:i:s.') . substr(microtime(true), -3);
    }

    private function getElapsedTime() {
        return round(microtime(true) - $this->startTime, 3);
    }

    private function logAuthStep($component, $action, $data) {
        error_log("AUTH_LOG [{$this->requestId}] [$component] [$action]: " . json_encode($data));
    }
}