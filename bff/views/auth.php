<?php
$title = 'Autenticação - BFF';
$currentPage = 'auth';
$extraCss = [
    '/assets/css/auth-monitor.css',
    '/assets/css/component-details.css'
];
$extraScripts = [
    '/assets/js/auth-monitor.js',
    '/assets/js/component-details.js'
];

ob_start();
?>

<div class="row g-4">
    <!-- Controles -->
    <?php include __DIR__ . '/partials/controls.php'; ?>

    <div class="col-md-9">
        <!-- Fluxo de Autenticação -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">
                    <i class="bi bi-diagram-3"></i>
                    Detalhes do Processo
                </h6>
            </div>
            <div class="card-body p-2">
                <!-- Diagrama do fluxo -->
                <div class="auth-flow-diagram mb-3">
                    <div class="component client" data-component="client">
                        <i class="bi bi-laptop"></i>
                        <span>Client</span>
                    </div>
                    <i class="bi bi-arrow-right flow-arrow"></i>
                    <div class="component bff" data-component="bff">
                        <i class="bi bi-box-arrow-in-down-right"></i>
                        <span>BFF</span>
                    </div>
                    <i class="bi bi-arrow-right flow-arrow"></i>
                    <div class="component kong" data-component="kong">
                        <i class="bi bi-shield-check"></i>
                        <span>Kong</span>
                    </div>
                    <i class="bi bi-arrow-right flow-arrow"></i>
                    <div class="component keycloak" data-component="keycloak">
                        <i class="bi bi-key"></i>
                        <span>Keycloak</span>
                    </div>
                    <i class="bi bi-arrow-right flow-arrow"></i>
                    <div class="component api" data-component="api">
                        <i class="bi bi-hdd-rack"></i>
                        <span>API</span>
                    </div>
                </div>

                <!-- Detalhes do componente -->
                <div id="componentDetails">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Clique em um componente para ver seus detalhes.
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Detalhadas -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">
                    <i class="bi bi-list-nested"></i>
                    Informações Detalhadas
                </h6>
                <div class="nav nav-tabs card-header-tabs">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#logTab">
                        <i class="bi bi-terminal"></i> Logs
                    </button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tokenTab">
                        <i class="bi bi-key"></i> Tokens
                    </button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#headerTab">
                        <i class="bi bi-card-text"></i> Headers
                    </button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dataTab">
                        <i class="bi bi-database"></i> Dados
                    </button>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="tab-content">
                    <!-- Tab de Logs -->
                    <div class="tab-pane fade show active" id="logTab">
                        <div id="authLogs" class="auth-logs"></div>
                    </div>

                    <!-- Tab de Tokens -->
                    <div class="tab-pane fade" id="tokenTab">
                        <div id="tokenInfo">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Faça uma autenticação para visualizar os tokens.
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Headers -->
                    <div class="tab-pane fade" id="headerTab">
                        <div id="headerInfo">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Faça uma autenticação para visualizar os headers.
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Dados -->
                    <div class="tab-pane fade" id="dataTab">
                        <div id="dataInfo">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Faça uma autenticação para visualizar os dados.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>