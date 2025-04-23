<?php
$title = 'Monitor de Serviços - BFF';
$currentPage = 'dashboard';
$extraCss = ['/assets/css/dashboard.css'];

ob_start();
?>

<!-- Cards de Status -->
<div class="row">
    <!-- BFF Status -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-box-arrow-in-down-right"></i> BFF
                </h5>
                <div class="status-indicator mt-3" id="bff-status">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted d-block">Porta: 8080</small>
                    <span id="bff-response-time" class="response-time">--ms</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kong Status -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-box-arrow-in-up-right"></i> Kong Gateway
                </h5>
                <div class="status-indicator mt-3" id="kong-status">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted d-block">Admin: 8001 | Proxy: 8000</small>
                    <span id="kong-response-time" class="response-time">--ms</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Keycloak Status -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-shield-lock"></i> Keycloak
                </h5>
                <div class="status-indicator mt-3" id="keycloak-status">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted d-block">Porta: 8082</small>
                    <span id="keycloak-response-time" class="response-time">--ms</span>
                </div>
            </div>
        </div>
    </div>

    <!-- API Status -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-hdd-rack"></i> API
                </h5>
                <div class="status-indicator mt-3" id="api-status">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted d-block">Porta: 8081</small>
                    <span id="api-response-time" class="response-time">--ms</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes dos Serviços -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Detalhes dos Serviços
                </h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleAutoRefresh()">
                        <i class="bi bi-clock"></i> Auto Refresh:
                        <span id="auto-refresh-status">ON</span>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportData()">
                        <i class="bi bi-download"></i> Exportar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="services-details">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>Status</th>
                                <th>Versão</th>
                                <th>Tempo de Resposta</th>
                                <th>Última Verificação</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preenchido via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botão de refresh flutuante -->
<div class="refresh-button" onclick="checkServices(true)">
    <i class="bi bi-arrow-clockwise"></i>
</div>

<?php
$content = ob_get_clean();

// Scripts específicos da página
ob_start();
?>
<script>
    // Inicialização
    document.addEventListener('DOMContentLoaded', () => {
        checkServices();
        refreshInterval = setInterval(checkServices, 30000);
    });
</script>
<?php
$pageScripts = ob_get_clean();

require_once __DIR__ . '/layout.php';
?>