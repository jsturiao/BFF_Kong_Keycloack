<div class="col-md-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white py-2">
            <h6 class="mb-0">
                <i class="bi bi-shield-lock"></i> Controles
            </h6>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-12">
                    <select class="form-select form-select-sm" id="flowType" onchange="window.authMonitor?.updateFormFields(this.value)">
                        <option value="password">Password Grant</option>
                        <option value="client">Client Credentials</option>
                        <option value="authorization">Authorization Code</option>
                    </select>
                </div>
                <div class="col-12">
                    <input type="text" class="form-control form-control-sm" id="clientId" value="app-demo" placeholder="Client ID">
                </div>
                <div class="col-12 auth-credential">
                    <input type="text" class="form-control form-control-sm" id="username" placeholder="Usuário">
                </div>
                <div class="col-12 auth-credential">
                    <input type="password" class="form-control form-control-sm" id="password" placeholder="Senha">
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm flex-grow-1" onclick="window.authMonitor.startAuthentication()">
                            <i class="bi bi-play-circle"></i> Autenticar
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.authMonitor.clearLogs()">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>