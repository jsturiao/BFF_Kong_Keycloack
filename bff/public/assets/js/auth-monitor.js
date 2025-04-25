class AuthMonitor {
    constructor() {
        this.currentFlow = null;
        this.stepDelay = 3000;
        this.components = ['client', 'bff', 'kong', 'keycloak', 'api'];
        this.componentDetails = window.componentDetails;
        
        const flowType = document.getElementById('flowType');
        if (flowType) {
            this.updateFormFields(flowType.value);
        }
    }

    updateFormFields(flowType) {
        const credentialFields = document.querySelectorAll('.auth-credential');
        
        credentialFields.forEach(field => {
            if (flowType === 'client') {
                field.style.display = 'none';
            } else {
                field.style.display = 'block';
            }
        });
    }

    async startAuthentication() {
        const flowType = document.getElementById('flowType')?.value || 'password';
        const clientId = document.getElementById('clientId')?.value || 'app-demo';
        const username = document.getElementById('username')?.value || '';
        const password = document.getElementById('password')?.value || '';

        if (flowType !== 'client' && (!username || !password)) {
            this.addLog('error', 'Usuário e senha são obrigatórios para este tipo de autenticação');
            return;
        }

        this.resetComponents();
        if (this.componentDetails) {
            this.componentDetails.clearEvents();
        }

        this.addLog('info', 'Iniciando novo fluxo de autenticação', {
            type: flowType,
            client_id: clientId,
            timestamp: new Date().toISOString()
        });

        const authButton = document.querySelector('.btn-primary');
        if (authButton) authButton.disabled = true;

        try {
            await this.processStep('client', 'Iniciando requisição de autenticação');
            await this.processStep('bff', 'Processando requisição e preparando para Kong');
            await this.processStep('kong', 'Validando requisição e encaminhando para Keycloak');
            await this.processStep('keycloak', 'Autenticando usuário');

            const tokens = {
                access_token: "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6InB1YmxpYy1rZXkifQ.eyJqdGkiOiJkMmY4Y2IyMC1mYzEwLTQwMzItYjFiYy0wODc3ODY0NWQ2YzEiLCJleHAiOjE2MTk3MzY5NjIsIm5iZiI6MCwiaWF0IjoxNjE5NzM2NjYyLCJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAiLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMTIzIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoiYXBwLWRlbW8iLCJhdXRoX3RpbWUiOjAsInNlc3Npb25fc3RhdGUiOiI5YzE3MzU2NS02N2M0LTQ0ZjktYmY3OC1kOGM3ZDNhZjE3ZTciLCJhY3IiOiIxIiwiYWxsb3dlZC1vcmlnaW5zIjpbIioiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJ1c2VyIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJwcm9maWxlIGVtYWlsIiwiZW1haWxfdmVyaWZpZWQiOmZhbHNlLCJuYW1lIjoiSm9obiBEb2UiLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJqb2huLmRvZSIsImdpdmVuX25hbWUiOiJKb2huIiwiZmFtaWx5X25hbWUiOiJEb2UiLCJlbWFpbCI6ImpvaG4uZG9lQGV4YW1wbGUuY29tIn0.ZGM3MjI1ZWUtZGM0Ny00YWQ5LWJlZjctYTBiNjI2ZjYyNzdk",
                refresh_token: "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6InJlZnJlc2gta2V5In0.eyJqdGkiOiJmMjM0MjM0Mi1jMjM0LTQyMzQtYjIzNC0yMzQyMzQyMzQyMzQiLCJleHAiOjE2MTk3NDAxNjIsIm5iZiI6MCwiaWF0IjoxNjE5NzM2NjYyLCJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAiLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMTIzIiwidHlwIjoiUmVmcmVzaCIsImF6cCI6ImFwcC1kZW1vIiwiYXV0aF90aW1lIjowLCJzZXNzaW9uX3N0YXRlIjoiOWMxNzM1NjUtNjdjNC00NGY5LWJmNzgtZDhjN2QzYWYxN2U3Iiwic2NvcGUiOiJwcm9maWxlIGVtYWlsIn0.MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkw",
                token_type: "Bearer",
                expires_in: 300,
                refresh_expires_in: 1800,
                scope: "profile email"
            };

            await this.processStep('keycloak', 'Gerando tokens de acesso', tokens);

            // Atualiza aba de tokens
            const tokenInfo = document.getElementById('tokenInfo');
            if (tokenInfo) {
                tokenInfo.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3">Access Token</h6>
                            <pre class="token-data">${tokens.access_token}</pre>
                            
                            <h6 class="card-subtitle mb-3 mt-4">Refresh Token</h6>
                            <pre class="token-data">${tokens.refresh_token}</pre>
                            
                            <div class="token-details mt-4">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Token Type:</strong> ${tokens.token_type}
                                    </div>
                                    <div class="col-6">
                                        <strong>Expires In:</strong> ${tokens.expires_in}s
                                    </div>
                                    <div class="col-6">
                                        <strong>Refresh Expires In:</strong> ${tokens.refresh_expires_in}s
                                    </div>
                                    <div class="col-6">
                                        <strong>Scope:</strong> ${tokens.scope}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            const headers = {
                'Authorization': `Bearer ${tokens.access_token}`,
                'X-Request-ID': `req_${Math.random().toString(36).substr(2)}`,
                'X-Client-ID': clientId,
                'Content-Type': 'application/json'
            };

            await this.processStep('kong', 'Validando tokens recebidos', {
                validation: {
                    tokens_valid: true,
                    signature_valid: true,
                    expiration_valid: true,
                    issuer_valid: true,
                    rate_limit_remaining: 999
                }
            });

            // Atualiza aba de headers
            const headerInfo = document.getElementById('headerInfo');
            if (headerInfo) {
                const headerRows = Object.entries(headers)
                    .map(([key, value]) => `
                        <tr>
                            <td><strong>${key}</strong></td>
                            <td>${value}</td>
                        </tr>
                    `).join('');

                headerInfo.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3">Request Headers</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>${headerRows}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            }

            await this.processStep('bff', 'Preparando requisição para API');

            const apiData = {
                status: "success",
                timestamp: new Date().toISOString(),
                data: {
                    user: {
                        id: 123,
                        name: "John Doe",
                        email: "john.doe@example.com",
                        roles: ["user", "admin"]
                    },
                    permissions: ["read", "write", "delete"],
                    session: {
                        created_at: new Date().toISOString(),
                        expires_in: 3600
                    }
                }
            };

            await this.processStep('api', 'Processando requisição autenticada', apiData);

            // Atualiza aba de dados
            const dataInfo = document.getElementById('dataInfo');
            if (dataInfo) {
                dataInfo.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3">Response Data</h6>
                            <pre class="data-json">${JSON.stringify(apiData, null, 2)}</pre>
                        </div>
                    </div>
                `;
            }

            await this.processStep('kong', 'Processando resposta da API');
            await this.processStep('bff', 'Finalizando processo');
            await this.processStep('client', 'Processando resposta final');

            this.addLog('success', 'Fluxo de autenticação concluído com sucesso', {
                requestId: `auth_${Math.random().toString(36).substr(2)}`,
                totalTime: 0.263
            });

        } catch (error) {
            console.error('Authentication error:', error);
            this.addLog('error', `Erro no processo de autenticação: ${error.message}`);
            this.markComponentError(this.getLastActiveComponent());
        } finally {
            if (authButton) authButton.disabled = false;
        }
    }

    async processStep(component, message, details = null) {
        this.markComponentProcessing(component);
        this.addLog('info', `[${component.toUpperCase()}] ${message}`, details);

        if (this.componentDetails) {
            this.componentDetails.addEvent(component, {
                type: 'process',
                level: 'info',
                message: message,
                details: details,
                timestamp: new Date().toLocaleTimeString()
            });
        }

        await this.delay(this.stepDelay);
        this.markComponentSuccess(component);
    }

    markComponentProcessing(component) {
        const el = document.querySelector(`.component.${component}`);
        if (el) {
            el.classList.remove('success', 'error');
            el.classList.add('processing');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    markComponentSuccess(component) {
        const el = document.querySelector(`.component.${component}`);
        if (el) {
            el.classList.remove('processing', 'error');
            el.classList.add('success');
        }
    }

    markComponentError(component) {
        const el = document.querySelector(`.component.${component}`);
        if (el) {
            el.classList.remove('processing', 'success');
            el.classList.add('error');
        }
    }

    resetComponents() {
        this.components.forEach(component => {
            const el = document.querySelector(`.component.${component}`);
            if (el) {
                el.classList.remove('processing', 'success', 'error');
            }
        });
    }

    getLastActiveComponent() {
        for (let i = this.components.length - 1; i >= 0; i--) {
            const el = document.querySelector(`.component.${this.components[i]}`);
            if (el?.classList.contains('processing')) {
                return this.components[i];
            }
        }
        return 'client';
    }

    addLog(level, message, details = null) {
        const logs = document.getElementById('authLogs');
        if (!logs) return;

        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry ${level}`;
        
        let detailsHtml = '';
        if (details) {
            detailsHtml = `
                <div class="log-details">
                    <pre class="json">${JSON.stringify(details, null, 2)}</pre>
                </div>
            `;
        }

        logEntry.innerHTML = `
            <div class="log-header">
                <span class="timestamp">${timestamp}</span>
                <span class="level ${level}">${level.toUpperCase()}</span>
                <span class="message">${message}</span>
            </div>
            ${detailsHtml}
        `;
        
        logs.appendChild(logEntry);
        logs.scrollTop = logs.scrollHeight;
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    clearLogs() {
        const logs = document.getElementById('authLogs');
        if (logs) logs.innerHTML = '';
        
        this.resetComponents();
        if (this.componentDetails) {
            this.componentDetails.clearEvents();
        }
        
        this.addLog('info', 'Sistema inicializado e pronto para autenticação');
    }
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing monitors...');
    window.componentDetails = new ComponentDetailsManager();
    window.authMonitor = new AuthMonitor();
});