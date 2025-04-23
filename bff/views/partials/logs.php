<div class="col-md-9">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0"><i class="bi bi-list-nested"></i> Detalhes do Processo</h6>
            <div class="btn-group btn-group-sm" role="group" aria-label="Process details tabs">
                <!-- Log de eventos -->
                <button class="btn btn-outline-secondary active" data-bs-toggle="tab" data-bs-target="#logTab" title="Log detalhado do fluxo de autenticação">
                    <i class="bi bi-terminal"></i> Logs
                </button>
                
                <!-- Tokens JWT -->
                <button class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#tokenTab" title="Tokens JWT (Access, Refresh, ID Token)">
                    <i class="bi bi-key"></i> Tokens
                </button>
                
                <!-- Headers HTTP -->
                <button class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#headersTab" title="Headers HTTP das requisições">
                    <i class="bi bi-card-text"></i> Headers
                </button>
                
                <!-- Dados da API -->
                <button class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#dataTab" title="Dados retornados pela API">
                    <i class="bi bi-database"></i> Dados
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="tab-content">
                <!-- Tab de Logs: histórico detalhado do fluxo -->
                <div class="tab-pane fade show active" id="logTab">
                    <div class="alert alert-info mb-2">
                        <i class="bi bi-info-circle"></i> Log de eventos do fluxo de autenticação, incluindo todas as etapas e detalhes técnicos.
                    </div>
                    <div id="authLogs" class="auth-logs">
                        <div class="log-entry">
                            <span class="timestamp"><?= date('H:i:s') ?></span>
                            <span class="level info">INFO</span>
                            <span class="message">Sistema inicializado e pronto para autenticação</span>
                        </div>
                    </div>
                </div>

                <!-- Tab de Tokens: detalhes dos JWTs -->
                <div class="tab-pane fade" id="tokenTab">
                    <div class="alert alert-info mb-2">
                        <i class="bi bi-info-circle"></i> Tokens JWT gerados pelo Keycloak, incluindo Access Token, Refresh Token e ID Token (quando aplicável).
                    </div>
                    <div id="tokenInfo">
                        <div class="alert alert-secondary">
                            Nenhum token gerado ainda. Inicie um fluxo de autenticação.
                        </div>
                    </div>
                </div>

                <!-- Tab de Headers: cabeçalhos HTTP -->
                <div class="tab-pane fade" id="headersTab">
                    <div class="alert alert-info mb-2">
                        <i class="bi bi-info-circle"></i> Headers HTTP das requisições, incluindo tokens de autenticação e headers de segurança.
                    </div>
                    <div id="headerInfo">
                        <div class="alert alert-secondary">
                            Nenhuma informação de headers disponível.
                        </div>
                    </div>
                </div>

                <!-- Tab de Dados: resposta da API -->
                <div class="tab-pane fade" id="dataTab">
                    <div class="alert alert-info mb-2">
                        <i class="bi bi-info-circle"></i> Dados retornados pela API após autenticação bem-sucedida.
                    </div>
                    <div id="dataInfo">
                        <div class="alert alert-secondary">
                            Nenhum dado da API disponível.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>