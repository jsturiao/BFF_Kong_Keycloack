<?php
$title = 'Monitor de Autenticação - BFF';
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

<!-- Container principal -->
<div class="auth-monitor">
    <!-- Fluxo de Autenticação (Horizontal) -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <h5 class="mb-0"><i class="bi bi-diagram-2"></i> Fluxo de Autenticação</h5>
        </div>
        <div class="card-body p-0">
            <div class="auth-flow-diagram">
                <div class="component client" data-status="idle" data-bs-toggle="modal" data-bs-target="#clientModal">
                    <i class="bi bi-laptop"></i> Cliente
                </div>
                <div class="flow-arrow">→</div>
                
                <div class="component bff" data-status="idle" data-bs-toggle="modal" data-bs-target="#bffModal">
                    <i class="bi bi-box-arrow-in-down-right"></i> BFF
                </div>
                <div class="flow-arrow">→</div>
                
                <div class="component kong" data-status="idle" data-bs-toggle="modal" data-bs-target="#kongModal">
                    <i class="bi bi-shield-check"></i> Kong
                </div>
                <div class="flow-arrow">→</div>
                
                <div class="component keycloak" data-status="idle" data-bs-toggle="modal" data-bs-target="#keycloakModal">
                    <i class="bi bi-key"></i> Keycloak
                </div>
                <div class="flow-arrow">→</div>
                
                <div class="component api" data-status="idle" data-bs-toggle="modal" data-bs-target="#apiModal">
                    <i class="bi bi-hdd-rack"></i> API
                </div>
            </div>
        </div>
    </div>

    <!-- Rest of the content (controls and logs) remains the same -->
    <?php include __DIR__ . '/partials/controls.php'; ?>
    <?php include __DIR__ . '/partials/logs.php'; ?>

    <!-- Component Details Modals -->
    <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalLabel">
                        <i class="bi bi-laptop"></i> Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="clientDetails">
                    <div class="placeholder-glow">
                        <div class="placeholder col-12 mb-3"></div>
                        <div class="placeholder col-8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bffModal" tabindex="-1" aria-labelledby="bffModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bffModalLabel">
                        <i class="bi bi-box-arrow-in-down-right"></i> BFF
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bffDetails">
                    <div class="placeholder-glow">
                        <div class="placeholder col-12 mb-3"></div>
                        <div class="placeholder col-8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kongModal" tabindex="-1" aria-labelledby="kongModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kongModalLabel">
                        <i class="bi bi-shield-check"></i> Kong Gateway
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="kongDetails">
                    <div class="placeholder-glow">
                        <div class="placeholder col-12 mb-3"></div>
                        <div class="placeholder col-8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="keycloakModal" tabindex="-1" aria-labelledby="keycloakModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="keycloakModalLabel">
                        <i class="bi bi-key"></i> Keycloak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="keycloakDetails">
                    <div class="placeholder-glow">
                        <div class="placeholder col-12 mb-3"></div>
                        <div class="placeholder col-8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="apiModal" tabindex="-1" aria-labelledby="apiModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="apiModalLabel">
                        <i class="bi bi-hdd-rack"></i> API
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="apiDetails">
                    <div class="placeholder-glow">
                        <div class="placeholder col-12 mb-3"></div>
                        <div class="placeholder col-8"></div>
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