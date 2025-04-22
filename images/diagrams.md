## 1. Visão Geral da Arquitetura
```mermaid
graph TB
    Cliente([Cliente])--> BFF
    subgraph Infraestrutura
        BFF --> Kong
        Kong --> Keycloak
        Kong --> API
        API --> DB[(Database)]
    end
    Keycloak --> KeycloakDB[(Keycloak DB)]
```

## 2. Fluxo de Autenticação
```mermaid
sequenceDiagram
    autonumber
    participant C as Cliente
    participant B as BFF
    participant K as Kong
    participant KC as Keycloak
    participant A as API

    C->>B: GET /produtos
    B->>KC: Solicita token (client credentials)
    KC-->>B: Retorna JWT
    B->>K: GET /api/produtos + JWT
    K->>K: Valida JWT
    K->>A: Request autenticado
    A-->>K: Response
    K-->>B: Response
    B-->>C: Dados formatados
```

## 3. Fluxo de Requisições
```mermaid
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

    subgraph Gateway
        Validate[Valida JWT]
        Route[Route to API]
    end

    Request --> Auth
    Auth --> Token
    Token --> Kong
    Kong --> Validate
    Validate --> Route
    Kong -.-> Direct
    Direct --> Format
    Route --> Format
```

## 4. Validação de Token
```mermaid
sequenceDiagram
    participant K as Kong
    participant KC as Keycloak
    
    K->>K: Extrai token do header
    K->>K: Decodifica JWT header
    K->>K: Obtém kid do header
    K->>KC: Obtém chave pública
    KC-->>K: Retorna chave
    K->>K: Valida assinatura
    K->>K: Verifica claims (exp, iss)
    K->>K: Autoriza request
```

## 5. Tratamento de Erros
```mermaid
flowchart TD
    A[Request] --> B{Kong OK?}
    B -->|Sim| C[Processa via Kong]
    B -->|Não| D{API Direta OK?}
    D -->|Sim| E[Processa via API]
    D -->|Não| F[Retorna Mock]
    
    C --> G{Response OK?}
    G -->|Sim| H[Retorna Dados]
    G -->|Não| D
```

## 6. Processo de Fallback
```mermaid
stateDiagram-v2
    [*] --> TentandoKong
    TentandoKong --> KongSucesso: OK
    TentandoKong --> TentandoAPI: Falha
    TentandoAPI --> APISucesso: OK
    TentandoAPI --> UsandoMock: Falha
    KongSucesso --> [*]: Retorna Dados
    APISucesso --> [*]: Retorna Dados
    UsandoMock --> [*]: Retorna Mock