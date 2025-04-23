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
            await this.processStep('client', 'Iniciando requisição de autenticação');
            await this.processStep('bff', 'Processando requisição e preparando para Kong');
            await this.processStep('kong', 'Validando requisição e encaminhando para Keycloak');
            await this.processStep('keycloak', 'Autenticando usuário');

            const response = await fetch('/auth/start', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ flowType, clientId, username, password })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Erro na autenticação');
            }

            await this.processStep('keycloak', 'Gerando tokens de acesso', {
                token_type: data.flow_details?.steps?.keycloak?.token_type,
                expires_in: data.flow_details?.steps?.keycloak?.expires_in
            });

            await this.processStep('kong', 'Validando tokens recebidos', {
                validation: data.flow_details?.steps?.kong_validation
            });

            await this.processStep('bff', 'Preparando requisição para API');
            await this.processStep('api', 'Processando requisição autenticada');
            await this.processStep('kong', 'Processando resposta da API');
            await this.processStep('bff', 'Finalizando processo');
            await this.processStep('client', 'Processando resposta final');

            this.addLog('success', 'Fluxo de autenticação concluído com sucesso', {
                requestId: data.request_id,
                totalTime: data.flow_details?.steps?.bff_final?.total_time
            });

        } catch (error) {
            this.addLog('error', `Erro no processo de autenticação: ${error.message}`);
            this.markComponentError(this.getLastActiveComponent());
        } finally {
            if (submitButton) submitButton.disabled = false;
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

        // Adiciona o evento ao componentDetails
        if (message.match(/\[(.*?)\]/)) {
            const componentType = message.match(/\[(.*?)\]/)[1].toLowerCase();
            if (this.componentDetails) {
                this.componentDetails.addEvent(componentType, {
                    level,
                    message: message.replace(/\[.*?\]\s*/, ''),
                    details,
                    timestamp
                });
            }
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.componentDetails = new ComponentDetailsManager();
    window.authMonitor = new AuthMonitor();
});