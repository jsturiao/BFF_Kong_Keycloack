class ComponentDetailsManager {
    constructor() {
        this.currentFlow = null;
        this.eventLogs = this.loadEvents() || {
            client: [],
            bff: [],
            kong: [],
            keycloak: [],
            api: []
        };
        this.initializeListeners();
    }

    loadEvents() {
        const stored = localStorage.getItem('authFlowEvents');
        return stored ? JSON.parse(stored) : null;
    }

    saveEvents() {
        localStorage.setItem('authFlowEvents', JSON.stringify(this.eventLogs));
    }

    initializeListeners() {
        document.querySelectorAll('.component').forEach(component => {
            component.addEventListener('click', (e) => {
                const componentType = component.classList[1];
                this.showComponentDetails(componentType);
            });
        });
    }

    showComponentDetails(componentType) {
        const modalId = `${componentType}Modal`;
        const detailsId = `${componentType}Details`;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        
        // Recarrega os eventos antes de mostrar
        this.eventLogs = this.loadEvents() || this.eventLogs;
        
        // Atualiza o conteúdo do modal
        this.updateModalContent(componentType, detailsId);
        
        // Mostra o modal
        modal.show();
    }

    updateModalContent(componentType, detailsId) {
        const detailsElement = document.getElementById(detailsId);
        if (!detailsElement) return;

        const details = this.getComponentDetails(componentType);
        detailsElement.innerHTML = this.formatDetails(details, componentType);

        // Após renderizar, ativa a tab de eventos
        const eventsTab = document.querySelector(`#tabs-${componentType} button[data-bs-target="#details-events-${componentType}"]`);
        if (eventsTab) {
            eventsTab.click();
        }
    }

    getComponentDetails(componentType) {
        const componentInfo = {
            client: {
                title: 'Cliente',
                description: 'Inicia o processo de autenticação',
                details: {
                    type: 'Frontend Application',
                    role: 'Initiator',
                    capabilities: [
                        'Inicia o fluxo de autenticação',
                        'Processa tokens',
                        'Gerencia sessão'
                    ]
                }
            },
            bff: {
                title: 'BFF (Backend for Frontend)',
                description: 'Intermediário entre o cliente e os serviços',
                details: {
                    type: 'API Gateway',
                    role: 'Intermediary',
                    capabilities: [
                        'Roteamento de requisições',
                        'Transformação de dados',
                        'Cache de respostas'
                    ]
                }
            },
            kong: {
                title: 'Kong Gateway',
                description: 'Gateway de API e gerenciador de requisições',
                details: {
                    type: 'API Gateway',
                    role: 'Security & Routing',
                    capabilities: [
                        'Rate limiting',
                        'Autenticação',
                        'Logging'
                    ]
                }
            },
            keycloak: {
                title: 'Keycloak',
                description: 'Servidor de autenticação e autorização',
                details: {
                    type: 'Identity Provider',
                    role: 'Authentication',
                    capabilities: [
                        'OAuth 2.0',
                        'OpenID Connect',
                        'Token Management'
                    ]
                }
            },
            api: {
                title: 'API',
                description: 'API de serviços protegida',
                details: {
                    type: 'REST API',
                    role: 'Resource Provider',
                    capabilities: [
                        'CRUD Operations',
                        'Business Logic',
                        'Data Access'
                    ]
                }
            }
        };

        return {
            ...componentInfo[componentType],
            events: this.eventLogs[componentType] || []
        };
    }

    formatDetails(details, componentType) {
        return `
            <div class="details-section">
                <h5>${details.title}</h5>
                <p class="text-muted">${details.description}</p>
                
                <div class="details-tabs">
                    <ul class="nav nav-tabs" id="tabs-${componentType}" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#details-main-${componentType}">
                                Detalhes
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#details-events-${componentType}">
                                Eventos (${details.events.length})
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade" id="details-main-${componentType}">
                            <div class="details-content">
                                <div class="component-info">
                                    <div class="info-item">
                                        <h6>Tipo</h6>
                                        <p>${details.details.type}</p>
                                    </div>
                                    <div class="info-item">
                                        <h6>Função</h6>
                                        <p>${details.details.role}</p>
                                    </div>
                                    <div class="info-item">
                                        <h6>Capacidades</h6>
                                        <ul>
                                            ${details.details.capabilities.map(cap => `<li>${cap}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade show active" id="details-events-${componentType}">
                            ${this.formatEvents(details.events)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    formatEvents(events) {
        if (!events || events.length === 0) {
            return '<div class="alert alert-info">Nenhum evento registrado</div>';
        }

        return `
            <div class="events-timeline">
                ${events.map(event => `
                    <div class="event-item ${event.level}">
                        <div class="event-time">${event.timestamp}</div>
                        <div class="event-message">${event.message}</div>
                        ${event.details ? `
                            <div class="event-details">
                                <pre class="json">${JSON.stringify(event.details, null, 2)}</pre>
                            </div>
                        ` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    }

    addEvent(componentType, event) {
        if (!this.eventLogs[componentType]) {
            this.eventLogs[componentType] = [];
        }

        this.eventLogs[componentType].push({
            timestamp: event.timestamp || new Date().toLocaleTimeString(),
            level: event.level || 'info',
            message: event.message,
            details: event.details
        });

        // Salva os eventos atualizados
        this.saveEvents();

        // Atualiza o modal se estiver aberto
        const modalElement = document.getElementById(`${componentType}Modal`);
        if (modalElement && modalElement.classList.contains('show')) {
            this.updateModalContent(componentType, `${componentType}Details`);
        }
    }

    clearEvents() {
        this.eventLogs = {
            client: [],
            bff: [],
            kong: [],
            keycloak: [],
            api: []
        };
        localStorage.removeItem('authFlowEvents');
    }
}

// Inicializa o gerenciador quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.componentDetails = new ComponentDetailsManager();
});