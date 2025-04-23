<!-- Detalhes e logs -->
<div class="col-md-9">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0"><i class="bi bi-list-nested"></i> Detalhes do Processo</h6>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" data-bs-toggle="tab" data-bs-target="#logTab">
                    <i class="bi bi-terminal"></i> Logs
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#tokenTab">
                    <i class="bi bi-key"></i> Tokens
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#headersTab">
                    <i class="bi bi-card-text"></i> Headers
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#dataTab">
                    <i class="bi bi-database"></i> Dados
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="tab-content">
                <!-- Tab de Logs -->
                <div class="tab-pane fade show active" id="logTab">
                    <div id="authLogs" class="auth-logs">
                        <div class="log-entry">
                            <span class="timestamp"><?= date('H:i:s') ?></span>
                            <span class="level info">INFO</span>
                            <span class="message">Sistema inicializado e pronto para autenticação</span>
                        </div>
                    </div>
                </div>

                <!-- Tab de Tokens -->
                <div class="tab-pane fade" id="tokenTab">
                    <div id="tokenInfo" class="token-info">
                        <div class="alert alert-info">
                            Nenhum token gerado ainda. Inicie um fluxo de autenticação.
                        </div>
                    </div>
                </div>

                <!-- Tab de Headers -->
                <div class="tab-pane fade" id="headersTab">
                    <div id="headerInfo" class="header-info">
                        <div class="alert alert-info">
                            Nenhuma informação de headers disponível.
                        </div>
                    </div>
                </div>

                <!-- Tab de Dados -->
                <div class="tab-pane fade" id="dataTab">
                    <div id="dataInfo" class="data-info">
                        <div class="alert alert-info">
                            Nenhum dado da API disponível.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>