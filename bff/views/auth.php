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

<div class="auth-monitor">
    <!-- Fluxo de Autenticação -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-diagram-2"></i> Fluxo de Autenticação</h5>
        </div>
        <div class="card-body p-0">
            <div class="auth-flow-diagram">
                <div class="component client">
                    <i class="bi bi-laptop"></i>
                    <span>Cliente</span>
                </div>
                
                <div class="flow-arrow">→</div>
                
                <div class="component bff">
                    <i class="bi bi-box-arrow-in-down-right"></i>
                    <span>BFF</span>
                </div>
                
                <div class="flow-arrow">→</div>
                
                <div class="component kong">
                    <i class="bi bi-shield-check"></i>
                    <span>Kong</span>
                </div>
                
                <div class="flow-arrow">→</div>
                
                <div class="component keycloak">
                    <i class="bi bi-key"></i>
                    <span>Keycloak</span>
                </div>
                
                <div class="flow-arrow">→</div>
                
                <div class="component api">
                    <i class="bi bi-hdd-rack"></i>
                    <span>API</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Controles e Logs -->
    <div class="row g-3">
        <?php include __DIR__ . '/partials/controls.php'; ?>
        <?php include __DIR__ . '/partials/logs.php'; ?>
    </div>

    <!-- Modais de detalhes -->
    <?php foreach (['client', 'bff', 'kong', 'keycloak', 'api'] as $component): ?>
    <div class="modal fade" id="<?= $component ?>Modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle"></i>
                        Detalhes do <?= ucfirst($component) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="<?= $component ?>Details"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.auth-flow-diagram .component').forEach(component => {
        component.addEventListener('click', () => {
            const componentType = component.classList[1];
            if (window.componentDetails) {
                window.componentDetails.showComponentDetails(componentType);
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>