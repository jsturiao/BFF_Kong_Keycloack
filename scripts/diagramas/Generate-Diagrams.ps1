# Script PowerShell para gerar diagramas Mermaid com tema claro

Write-Host "=== Gerando diagramas PNG ===" -ForegroundColor Cyan

# Verifica se o mmdc está instalado
try {
    $null = Get-Command mmdc -ErrorAction Stop
}
catch {
    Write-Host "Erro: mermaid-cli não encontrado" -ForegroundColor Red
    Write-Host "Por favor, instale com: npm install -g @mermaid-js/mermaid-cli" -ForegroundColor Yellow
    exit 1
}

# Cria diretório para as imagens
$null = New-Item -ItemType Directory -Force -Path "images\png"

# Função para gerar diagrama
function Generate-Diagram {
    param (
        [string]$Name,
        [string]$Content
    )
    Write-Host "Gerando $Name.png..." -ForegroundColor Green
    $Content | Out-File -Encoding UTF8 "temp.mmd"
    mmdc -i temp.mmd -o "images\png\$Name.png" -t neutral
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ $Name.png gerado com sucesso" -ForegroundColor Green
    }
    else {
        Write-Host "✗ Erro ao gerar $Name.png" -ForegroundColor Red
    }
}

# Diagrama 1: Arquitetura de Alto Nível
$diagram1 = @"
graph TB
    Cliente([Cliente])--> BFF
    subgraph Infraestrutura
        BFF --> Kong
        Kong --> Keycloak
        Kong --> API
        API --> DB[(Database)]
    end
    Keycloak --> KeycloakDB[(Keycloak DB)]
"@
Generate-Diagram -Name "high-level-architecture" -Content $diagram1

# Diagrama 2: Fluxo de Autenticação
$diagram2 = @"
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
"@
Generate-Diagram -Name "authentication-flow" -Content $diagram2

# Diagrama 3: Fluxo de Requisições
$diagram3 = @"
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
"@
Generate-Diagram -Name "request-flow" -Content $diagram3

# Diagrama 4: Validação de Token
$diagram4 = @"
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
"@
Generate-Diagram -Name "token-validation-flow" -Content $diagram4

# Diagrama 5: Tratamento de Erros
$diagram5 = @"
flowchart TD
    A[Request] --> B{Kong OK?}
    B -->|Sim| C[Via Kong]
    B -->|Não| D{API OK?}
    D -->|Sim| E[Via API]
    D -->|Não| F[Mock]
"@
Generate-Diagram -Name "error-handling-flow" -Content $diagram5

# Diagrama 6: Fallback
$diagram6 = @"
stateDiagram-v2
    [*] --> TentandoKong
    TentandoKong --> KongSucesso: OK
    TentandoKong --> TentandoAPI: Falha
    TentandoAPI --> APISucesso: OK
    TentandoAPI --> UsandoMock: Falha
    KongSucesso --> [*]
    APISucesso --> [*]
    UsandoMock --> [*]
"@
Generate-Diagram -Name "fallback-flow" -Content $diagram6

# Limpa arquivo temporário
Remove-Item -Force "temp.mmd" -ErrorAction SilentlyContinue

Write-Host "`n=== Diagramas gerados com sucesso ===" -ForegroundColor Cyan
Get-ChildItem "images\png\*.png" | Format-Table Name, Length, LastWriteTime