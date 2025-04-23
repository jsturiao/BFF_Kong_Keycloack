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

            // Mock de tokens para exibição
            const mockTokens = {
                access_token: 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJoLi4u',
                refresh_token: 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJoLi4u',
                token_type: 'Bearer',
                expires_in: 300
            };

            await this.processStep('keycloak', 'Gerando tokens de acesso', mockTokens);

            // Atualiza aba de tokens
            const tokenInfo = document.getElementById('tokenInfo');
            if (tokenInfo) {
                tokenInfo.innerHTML = this.formatTokenSection(mockTokens);
                // Exibe tokens automaticamente
                document.querySelector('[data-bs-target="#tokenTab"]').click();
            }

            await this.processStep('kong', 'Validando tokens recebidos', {
                validation: data.flow_details?.steps?.kong_validation
            });

            // Atualiza aba de headers
            const headerInfo = document.getElementById('headerInfo');
            if (headerInfo) {
                headerInfo.innerHTML = this.formatHeaderSection(data);
            }

            // Mock de produtos
            const mockProducts = [
                { id: 1, name: 'Produto 1', price: 99.99, category: 'Eletrônicos' },
                { id: 2, name: 'Produto 2', price: 149.99, category: 'Eletrônicos' },
                { id: 3, name: 'Produto 3', price: 199.99, category: 'Acessórios' }
            ];

            await this.processStep('api', 'Processando requisição autenticada', {
                endpoint: '/api/products',
                method: 'GET',
                response: {
                    total: mockProducts.length,
                    products: mockProducts
                }
            });

            // Atualiza aba de dados
            const dataInfo = document.getElementById('dataInfo');
            if (dataInfo) {
                dataInfo.innerHTML = this.formatDataSection(mockProducts);
                // Exibe dados automaticamente
                document.querySelector('[data-bs-target="#dataTab"]').click();
            }

            await this.processStep('kong', 'Processando resposta da API');
            await this.processStep('bff', 'Finalizando processo');
            await this.processStep('client', 'Processando resposta final');

            this.addLog('success', 'Fluxo de autenticação concluído com sucesso', {
                requestId: data.request_id,
                totalTime: data.flow_details?.steps?.bff_final?.total_time,
                products_loaded: mockProducts.length
            });

        } catch (error) {
            this.addLog('error', `Erro no processo de autenticação: ${error.message}`);
            this.markComponentError(this.getLastActiveComponent());
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    }

    formatTokenSection(tokens) {
        return `
            <div class="data-section">
                <h6>Access Token</h6>
                <div class="mb-4">
                    <strong class="d-block mb-2">Token Details</strong>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Token Type</th>
                                <td>${tokens.token_type}</td>
                            </tr>
                            <tr>
                                <th>Expires In</th>
                                <td>${tokens.expires_in} segundos</td>
                            </tr>
                            <tr>
                                <th>Issued At</th>
                                <td>${new Date().toLocaleString()}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <strong class="d-block mb-2">Access Token</strong>
                    <pre class="json">${tokens.access_token}</pre>
                </div>

                <div>
                    <strong class="d-block mb-2">Refresh Token</strong>
                    <pre class="json">${tokens.refresh_token}</pre>
                </div>
            </div>
        `;
    }

    formatHeaderSection(data) {
        return `
            <div class="data-section">
                <h6>Headers de Segurança</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Header</th>
                                <th>Valor</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Authorization</td>
                                <td>Bearer ${data.flow_details?.steps?.keycloak?.access_token?.substring(0, 20)}...</td>
                                <td>Token de acesso para autenticação</td>
                            </tr>
                            <tr>
                                <td>X-Request-ID</td>
                                <td>${data.request_id}</td>
                                <td>Identificador único da requisição</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <h6>Métricas de Validação</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th width="200">Rate Limit Remaining</th>
                                    <td>${data.flow_details?.steps?.kong_validation?.validation?.rate_limit_remaining}</td>
                                </tr>
                                <tr>
                                    <th>Cache Status</th>
                                    <td>${data.flow_details?.steps?.kong_validation?.validation?.cache_status}</td>
                                </tr>
                                <tr>
                                    <th>Validation Timestamp</th>
                                    <td>${data.flow_details?.steps?.kong_validation?.validation?.validation_timestamp}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    formatDataSection(products) {
        return `
            <div class="data-section">
                <h6>Lista de Produtos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Categoria</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${products.map(product => `
                                <tr>
                                    <td>${product.id}</td>
                                    <td>${product.name}</td>
                                    <td>R$ ${product.price.toFixed(2)}</td>
                                    <td>${product.category}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Total de produtos: ${products.length}
                    </small>
                </div>
            </div>
        `;
    }

    // ... (outros métodos mantidos como estão)

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

    clearLogs() {
        const elements = {
            logs: document.getElementById('authLogs'),
            tokenInfo: document.getElementById('tokenInfo'),
            headerInfo: document.getElementById('headerInfo'),
            dataInfo: document.getElementById('dataInfo')
        };

        if (elements.logs) elements.logs.innerHTML = '';
        if (elements.tokenInfo) {
            elements.tokenInfo.innerHTML = '<div class="alert alert-info">Nenhum token gerado ainda.</div>';
        }
        if (elements.headerInfo) {
            elements.headerInfo.innerHTML = '<div class="alert alert-info">Nenhuma informação de headers disponível.</div>';
        }
        if (elements.dataInfo) {
            elements.dataInfo.innerHTML = '<div class="alert alert-info">Nenhum dado da API disponível.</div>';
        }

        this.resetComponents();
        if (this.componentDetails) {
            this.componentDetails.clearEvents();
        }
        this.addLog('info', 'Sistema inicializado e pronto para autenticação');
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.componentDetails = new ComponentDetailsManager();
    window.authMonitor = new AuthMonitor();
});