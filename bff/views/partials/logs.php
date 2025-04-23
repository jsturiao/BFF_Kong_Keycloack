<div class="col-md-9">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0"><i class="bi bi-list-nested"></i> Detalhes do Processo</h6>
            <div class="nav nav-tabs card-header-tabs">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#logTab">
                    <i class="bi bi-terminal"></i> Logs
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tokenTab">
                    <i class="bi bi-key"></i> Tokens
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#headersTab">
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
                    <div id="tokenInfo">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Aqui serão exibidos os tokens JWT gerados durante a autenticação:
                            <ul class="mt-2">
                                <li>Access Token</li>
                                <li>Refresh Token</li>
                                <li>Token Details</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tab de Headers -->
                <div class="tab-pane fade" id="headersTab">
                    <div id="headerInfo">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Aqui serão exibidos os headers HTTP das requisições:
                            <ul class="mt-2">
                                <li>Authorization</li>
                                <li>Security Headers</li>
                                <li>Response Headers</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tab de Dados -->
                <div class="tab-pane fade" id="dataTab">
                    <div id="dataInfo">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Aqui serão exibidos os dados retornados pela API:
                            <ul class="mt-2">
                                <li>Dados do Usuário</li>
                                <li>Métricas da Requisição</li>
                                <li>Status da Resposta</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>