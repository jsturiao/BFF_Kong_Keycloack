class ComponentDetailsManager {
    constructor() {
        this.events = new Map();
        this.initializeEvents();
    }

    initializeEvents() {
        // Limpa eventos existentes
        this.events.clear();

        // Inicializa array de eventos para cada componente
        ['client', 'bff', 'kong', 'keycloak', 'api'].forEach(component => {
            this.events.set(component, []);
        });

        // Adiciona listeners aos componentes
        document.querySelectorAll('.auth-flow-diagram .component').forEach(component => {
            component.addEventListener('click', (e) => {
                const componentType = e.currentTarget.getAttribute('data-component');
                if (componentType) {
                    // Remove classe active de todos os componentes
                    document.querySelectorAll('.auth-flow-diagram .component').forEach(c => {
                        c.classList.remove('active');
                    });
                    // Adiciona classe active ao componente clicado
                    e.currentTarget.classList.add('active');
                    // Mostra detalhes
                    this.showComponentDetails(componentType);
                }
            });
        });
    }

    addEvent(component, event) {
        // Verifica se já existe um evento idêntico no mesmo timestamp
        const events = this.events.get(component) || [];
        const isDuplicate = events.some(existingEvent => 
            existingEvent.message === event.message && 
            existingEvent.timestamp === event.timestamp
        );

        if (!isDuplicate) {
            events.push({
                ...event,
                id: `${component}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
            });
            this.events.set(component, events);
            
            // Se o componente estiver ativo, atualiza os detalhes
            const componentElement = document.querySelector(`.component[data-component="${component}"]`);
            if (componentElement?.classList.contains('active')) {
                this.showComponentDetails(component);
            }
        }
    }

    showComponentDetails(component) {
        const events = this.events.get(component) || [];
        const detailsContainer = document.getElementById('componentDetails');
        
        if (detailsContainer && events.length > 0) {
            const eventsList = events.map(event => `
                <div class="event-item ${event.level || 'info'}">
                    <div class="event-header">
                        <span class="timestamp">${event.timestamp}</span>
                        <span class="message">${event.message}</span>
                    </div>
                    ${event.details ? `
                        <div class="event-details">
                            <pre class="language-json">${JSON.stringify(event.details, null, 2)}</pre>
                        </div>
                    ` : ''}
                </div>
            `).join('');

            detailsContainer.innerHTML = `
                <div class="component-info">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-info-circle"></i>
                        Eventos do componente: ${component.toUpperCase()}
                    </h6>
                    <div class="events-list">
                        ${eventsList}
                    </div>
                </div>
            `;

            // Scroll para o componente selecionado
            detailsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else if (detailsContainer) {
            detailsContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Nenhum evento registrado para o componente ${component.toUpperCase()}.
                </div>
            `;
        }
    }

    clearEvents() {
        this.events.clear();
        // Remove classe active de todos os componentes
        document.querySelectorAll('.auth-flow-diagram .component').forEach(c => {
            c.classList.remove('active');
        });
        // Limpa o container de detalhes
        const detailsContainer = document.getElementById('componentDetails');
        if (detailsContainer) {
            detailsContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Selecione um componente para ver seus detalhes.
                </div>
            `;
        }
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.componentDetails = new ComponentDetailsManager();
});