class AuthMonitor {
    constructor() {
        this.currentFlow = null;
        this.stepDelay = 3000;
        this.components = ['client', 'bff', 'kong', 'keycloak', 'api'];
        this.componentDetails = window.componentDetails;
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        document.getElementById('authForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.startAuthentication();
        });

        document.getElementById('flowType')?.addEventListener('change', (e) => {
            this.updateFormFields(e.target.value);
        });
    }

    updateFormFields(flowType) {
        const credentialFields = document.querySelectorAll('.auth-credential');
        credentialFields.forEach(field => {
            field.style.display = flowType === 'client' ? 'none' : 'block';
        });
    }

    async startAuthentication() {
        const flowType = document.getElementById('flowType')?.value;
        const clientId = document.getElementById('clientId')?.value;
        const username = document.getElementById('username')?.value;
        const password = document.getElementById('password')?.value;

        this.resetComponents();
        
        // Garante que o componentDetails existe
        if (!this.componentDetails) {
            this.componentDetails = window.componentDetails;
        }

        // Limpa eventos anteriores
        if (this.componentDetails) {
            this.componentDetails.clearEvents();
        }

        this.addLog('info', 'Iniciando novo fluxo de autenticação', {
            type: flowType,
            client_id: clientId,
            timestamp: new Date().toISOString()
        });

        const submitButton = document.querySelector('#authForm button[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            await this.executeAuthenticationFlow(flowType, clientId, username, password);
        } catch (error) {
            this.addLog('error', `Erro no processo de autenticação: ${error.message}`);
            this.markComponentError(this.getLastActiveComponent());
            
            if (this.componentDetails) {
                this.componentDetails.addEvent(this.getLastActiveComponent(), {
                    type: 'error',
                    level: 'error',
                    message: error.message,
                    details: { stack: error.stack },
                    timestamp: new Date().toLocaleTimeString()
                });
            }
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    }

    async executeAuthenticationFlow(flowType, clientId, username, password) {
        // 1. Cliente inicia
        await this.processStep('client', 'Iniciando requisição de autenticação');

        // 2. BFF prepara
        await this.processStep('bff', 'Processando requisição e preparando para Kong');

        // 3. Kong processa
        await this.processStep('kong', 'Validando requisição e encaminhando para Keycloak');

        // 4. Keycloak autentica
        await this.processStep('keycloak', 'Autenticando usuário');

        // Faz a requisição real
        const authResponse = await fetch('/auth/start', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ flowType, clientId, username, password })
        });

        const data = await authResponse.json();
        if (!data.success && !data.flow_details) {
            throw new Error(data.message || 'Erro na autenticação');
        }

        // 5. Keycloak gera tokens
        await this.processStep('keycloak', 'Gerando tokens de acesso', {
            token_type: data.flow_details?.steps?.keycloak?.token_type,
            expires_in: data.flow_details?.steps?.keycloak?.expires_in
        });

        // 6. Kong valida tokens
        await this.processStep('kong', 'Validando tokens recebidos', {
            validation: data.flow_details?.steps?.kong_validation
        });

        // 7. BFF prepara chamada API
        await this.processStep('bff', 'Preparando requisição para API');

        // 8. API processa
        await this.processStep('api', 'Processando requisição autenticada', {
            endpoint: '/api/data',
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${data.flow_details?.steps?.keycloak?.access_token}`
            }
        });

        // 9. API retorna dados
        await this.processStep('api', 'Retornando dados solicitados', {
            data: {
                user: {
                    id: 123,
                    name: 'Test User',
                    email: 'test@example.com'
                }
            }
        });

        // 10. Kong processa resposta
        await this.processStep('kong', 'Processando resposta da API');

        // 11. BFF finaliza
        await this.processStep('bff', 'Finalizando processo', {
            metrics: data.flow_details?.steps?.bff_final
        });

        // 12. Cliente processa resposta
        await this.processStep('client', 'Processando resposta final');

        // Atualiza visualizações
        if (data.flow_details?.steps) {
            this.displayTokenInfo(data.flow_details.steps.keycloak);
            this.displayHeaderInfo(data.flow_details);
        }

        this.addLog('success', 'Fluxo de autenticação concluído com sucesso', {
            requestId: data.request_id,
            totalTime: data.flow_details?.steps?.bff_final?.total_time
        });
    }

    async processStep(component, message, details = null) {
        this.markComponentProcessing(component);
        this.addLog('info', `[${component.toUpperCase()}] ${message}`, details);

        // Garante que o componentDetails existe
        if (!this.componentDetails) {
            this.componentDetails = window.componentDetails;
        }

        // Adiciona o evento ao componente específico
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

        // Adiciona evento de sucesso
        if (this.componentDetails) {
            this.componentDetails.addEvent(component, {
                type: 'status',
                level: 'success',
                message: 'Etapa concluída com sucesso',
                details: {
                    step: message,
                    status: 'success',
                    timestamp: new Date().toISOString()
                }
            });
        }
    }

    // ... (resto dos métodos mantidos como estão)

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
            <div class="log-header" onclick="this.parentElement.classList.toggle('expanded')">
                <span class="timestamp">${timestamp}</span>
                <span class="level ${level}">${level.toUpperCase()}</span>
                <span class="message">${message}</span>
                <span class="btn-toggle">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            ${detailsHtml}
        `;
        
        logs.appendChild(logEntry);
        logs.scrollTop = logs.scrollHeight;

        // Adiciona o evento ao componente correspondente
        if (message.match(/\[(.*?)\]/)) {
            const componentType = message.match(/\[(.*?)\]/)[1].toLowerCase();
            
            // Garante que o componentDetails existe
            if (!this.componentDetails) {
                this.componentDetails = window.componentDetails;
            }
            
            // Adiciona o evento ao gerenciador de detalhes
            if (this.componentDetails) {
                this.componentDetails.addEvent(componentType, {
                    type: 'log',
                    level: level,
                    message: message.replace(/\[.*?\]\s*/, ''),
                    details: details,
                    timestamp: timestamp
                });
            }
        }
    }

    displayTokenInfo(tokenData) {
        // ... (método mantido como está)
    }

    displayHeaderInfo(flowDetails) {
        // ... (método mantido como está)
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    markComponentProcessing(component) {
        // ... (método mantido como está)
    }

    markComponentSuccess(component) {
        // ... (método mantido como está)
    }

    markComponentError(component) {
        // ... (método mantido como está)
    }

    resetComponents() {
        this.components.forEach(component => {
            const el = document.querySelector(`.component.${component}`);
            if (el) el.dataset.status = 'idle';
        });
    }

    getLastActiveComponent() {
        for (let i = this.components.length - 1; i >= 0; i--) {
            const el = document.querySelector(`.component.${this.components[i]}`);
            if (el?.dataset.status === 'processing') {
                return this.components[i];
            }
        }
        return 'client';
    }

    clearLogs() {
        const elements = {
            logs: document.getElementById('authLogs'),
            tokenInfo: document.getElementById('tokenInfo'),
            headerInfo: document.getElementById('headerInfo')
        };

        if (elements.logs) elements.logs.innerHTML = '';
        if (elements.tokenInfo) {
            elements.tokenInfo.innerHTML = `
                <div class="alert alert-info">
                    Nenhum token gerado ainda. Inicie um fluxo de autenticação.
                </div>
            `;
        }
        if (elements.headerInfo) {
            elements.headerInfo.innerHTML = `
                <div class="alert alert-info">
                    Nenhuma informação de headers disponível.
                </div>
            `;
        }

        this.resetComponents();
        if (this.componentDetails) {
            this.componentDetails.clearEvents();
        }
        this.addLog('info', 'Sistema inicializado e pronto para autenticação');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.componentDetails = new ComponentDetailsManager();
    window.authMonitor = new AuthMonitor();
});