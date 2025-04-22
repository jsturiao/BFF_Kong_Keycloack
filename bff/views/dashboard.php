<?php
// Obtém o hostname do servidor para usar nas URLs
$serverHost = $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor de Serviços - BFF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/dashboard.html.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let autoRefresh = true;
        let refreshInterval;

        function updateServiceStatus(service, serviceData) {
            const statusDiv = document.getElementById(`${service}-status`);
            const responseTimeSpan = document.getElementById(`${service}-response-time`);
            
            // Log para debug
            console.log(`Atualizando status do serviço ${service}:`, serviceData);
            
            // Garante que temos valores padrão
            const status = serviceData?.status ?? false;
            const responseTime = serviceData?.responseTime;
            const version = serviceData?.version ?? 'N/A';
            
            // Atualiza indicador de status
            const statusClass = status ? 'status-online' : 'status-offline';
            const statusIcon = status ? 'check-circle-fill' : 'x-circle-fill';
            
            statusDiv.innerHTML = `
                <i class="bi bi-${statusIcon} fs-1 ${statusClass}"></i>
                <div class="mt-2">
                    <span class="${statusClass}">${status ? 'Online' : 'Offline'}</span>
                </div>
            `;

            // Atualiza tempo de resposta
            if (responseTime) {
                const responseClass = responseTime < 300 ? 'fast' : responseTime < 1000 ? 'medium' : 'slow';
                responseTimeSpan.className = `response-time ${responseClass}`;
                responseTimeSpan.innerHTML = `<i class="bi bi-clock"></i> ${responseTime}ms`;
            } else {
                responseTimeSpan.innerHTML = '--ms';
            }

            // Atualiza tabela de detalhes
            updateServiceDetails(service, {
                status,
                version,
                responseTime,
                lastCheck: new Date().toLocaleTimeString(),
                error: serviceData?.error
            });
        }

        function updateServiceDetails(service, data) {
            const tbody = document.querySelector('#services-details tbody');
            let row = tbody.querySelector(`[data-service="${service}"]`);
            
            if (!row) {
                row = tbody.insertRow();
                row.setAttribute('data-service', service);
            }

            const statusClass = data.status ? 'online' : 'offline';
            const statusText = data.status ? 'Online' : 'Offline';
            const responseTimeText = data.responseTime ? `${data.responseTime}ms` : '--';

            row.innerHTML = `
                <td>${service.toUpperCase()}</td>
                <td><span class="service-status ${statusClass}">${statusText}</span></td>
                <td>${data.version || 'N/A'}</td>
                <td>
                    <span class="response-time ${data.responseTime ? (data.responseTime < 300 ? 'fast' : data.responseTime < 1000 ? 'medium' : 'slow') : ''}">
                        ${responseTimeText}
                    </span>
                </td>
                <td>${data.lastCheck}</td>
                <td>
                    <button class="btn btn-sm btn-outline-info" onclick='showDetails(${JSON.stringify(data)})'>
                        <i class="bi bi-eye"></i>
                    </button>
                    ${data.error ? `<i class="bi bi-exclamation-triangle text-warning ms-2" title="${data.error}"></i>` : ''}
                </td>
            `;
        }

        function checkServices(manual = false) {
            if (manual) {
                const refreshButton = document.querySelector('.refresh-button');
                refreshButton.classList.add('spinning');
                setTimeout(() => refreshButton.classList.remove('spinning'), 1000);
            }

            fetch('/status')
                .then(response => response.json())
                .then(data => {
                    console.log('Status dos serviços:', data);
                    
                    // Atualiza status de cada serviço
                    Object.entries(data.services).forEach(([service, serviceData]) => {
                        updateServiceStatus(service, serviceData);
                    });

                    // Atualiza timestamp
                    document.getElementById('last-update').textContent = 
                        `Última atualização: ${new Date().toLocaleTimeString()}`;

                    if (data.success) {
                        showAlert('Status dos serviços atualizado', 'success');
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar serviços:', error);
                    showAlert('Erro ao verificar status dos serviços', 'danger');
                });
        }

        function showAlert(message, type = 'info') {
            const alertsDiv = document.getElementById('alerts');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertsDiv.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        }

        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            const status = document.getElementById('auto-refresh-status');
            status.textContent = autoRefresh ? 'ON' : 'OFF';
            
            if (autoRefresh) {
                refreshInterval = setInterval(checkServices, 30000);
                showAlert('Auto refresh ativado', 'success');
            } else {
                clearInterval(refreshInterval);
                showAlert('Auto refresh desativado', 'warning');
            }
        }

        function showDetails(data) {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detalhes do Serviço</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <pre class="bg-light p-3 rounded">${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        }

        function exportData() {
            const table = document.getElementById('services-details');
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            
            const data = rows.map(row => ({
                service: row.cells[0].textContent,
                status: row.cells[1].textContent.trim(),
                version: row.cells[2].textContent,
                responseTime: row.cells[3].textContent.trim(),
                lastCheck: row.cells[4].textContent
            }));

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `services-status-${new Date().toISOString()}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showAlert('Dados exportados com sucesso', 'success');
        }

        // Inicialização
        checkServices();
        refreshInterval = setInterval(checkServices, 30000);

        // Log para debug
        console.log('Dashboard inicializado.');
    </script>
</body>
</html>