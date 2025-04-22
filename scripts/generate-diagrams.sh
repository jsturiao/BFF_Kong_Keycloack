#!/bin/bash

echo "=== Gerando diagramas PNG ==="

# Verifica se o mmdc está instalado
if ! command -v mmdc &> /dev/null; then
    echo "Erro: mermaid-cli não encontrado"
    echo "Por favor, instale com: npm install -g @mermaid-js/mermaid-cli"
    exit 1
fi

# Cria diretório para as imagens
mkdir -p images/png

# Diagrama 1: Arquitetura de Alto Nível
echo "Gerando high-level-architecture.png..."
cat > temp.mmd << 'EOF'
graph TB
    Cliente([Cliente])--> BFF
    subgraph Infraestrutura
        BFF --> Kong
        Kong --> Keycloak
        Kong --> API
        API --> DB[(Database)]
    end
    Keycloak --> KeycloakDB[(Keycloak DB)]
EOF
mmdc -i temp.mmd -o images/png/high-level-architecture.png -t neutral

# Diagrama 2: Fluxo de Autenticação
echo "Gerando authentication-flow.png..."
cat > temp.mmd << 'EOF'
sequenceDiagram
    autonumber
    participant C as Cliente
    participant B as BFF
    participant K as Kong
    participant KC as Keycloak
    participant A as API

    C->>B: GET /produtos
    B->>KC: Solicita token
    KC-->>B: Retorna JWT
    B->>K: GET /api/produtos + JWT
    K->>K: Valida JWT
    K->>A: Request autenticado
    A-->>K: Response
    K-->>B: Response
    B-->>C: Dados formatados
EOF
mmdc -i temp.mmd -o images/png/authentication-flow.png -t neutral

# Diagrama 3: Fluxo de Requisições
echo "Gerando request-flow.png..."
cat > temp.mmd << 'EOF'
graph LR
    subgraph Cliente
        Request[Request /produtos]
    end

    subgraph BFF
        Auth[Verifica Auth]
        Token[Obtém Token]
        Kong[Tenta Kong]
        Direct[Tenta API Direta]
        Format[Formata Resposta]
    end

    Request --> Auth
    Auth --> Token
    Token --> Kong
    Kong --> Direct
    Direct --> Format
EOF
mmdc -i temp.mmd -o images/png/request-flow.png -t neutral

# Diagrama 4: Validação de Token
echo "Gerando token-validation-flow.png..."
cat > temp.mmd << 'EOF'
sequenceDiagram
    participant K as Kong
    participant KC as Keycloak
    
    K->>K: Extrai token do header
    K->>K: Decodifica JWT
    K->>K: Obtém kid
    K->>KC: Obtém chave pública
    KC-->>K: Retorna chave
    K->>K: Valida assinatura
    K->>K: Verifica claims
EOF
mmdc -i temp.mmd -o images/png/token-validation-flow.png -t neutral

# Diagrama 5: Tratamento de Erros
echo "Gerando error-handling-flow.png..."
cat > temp.mmd << 'EOF'
flowchart TD
    A[Request] --> B{Kong OK?}
    B -->|Sim| C[Via Kong]
    B -->|Não| D{API OK?}
    D -->|Sim| E[Via API]
    D -->|Não| F[Mock]
EOF
mmdc -i temp.mmd -o images/png/error-handling-flow.png -t neutral

# Diagrama 6: Fallback
echo "Gerando fallback-flow.png..."
cat > temp.mmd << 'EOF'
stateDiagram-v2
    [*] --> TentandoKong
    TentandoKong --> KongSucesso: OK
    TentandoKong --> TentandoAPI: Falha
    TentandoAPI --> APISucesso: OK
    TentandoAPI --> UsandoMock: Falha
    KongSucesso --> [*]
    APISucesso --> [*]
    UsandoMock --> [*]
EOF
mmdc -i temp.mmd -o images/png/fallback-flow.png -t neutral

# Limpa arquivo temporário
rm temp.mmd

echo -e "\n=== Diagramas gerados com sucesso ==="
ls -l images/png/