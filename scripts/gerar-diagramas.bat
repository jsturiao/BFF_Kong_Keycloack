@echo off
echo === Gerando diagramas PNG ===

REM Verifica se o mmdc está instalado
where mmdc >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Erro: mermaid-cli nao encontrado
    echo Por favor, instale com: npm install -g @mermaid-js/mermaid-cli
    exit /b 1
)

REM Cria diretório para as imagens
mkdir images\png 2>nul

REM Diagrama 1: Arquitetura de Alto Nível
echo Gerando high-level-architecture.png...
(
echo graph TB
echo     Cliente([Cliente]^)-^-> BFF
echo     subgraph Infraestrutura
echo         BFF --^> Kong
echo         Kong --^> Keycloak
echo         Kong --^> API
echo         API --^> DB[(Database^)]
echo     end
echo     Keycloak --^> KeycloakDB[(Keycloak DB^)]
) > temp.mmd
mmdc -i temp.mmd -o images/png/high-level-architecture.png -t neutral

REM Diagrama 2: Fluxo de Autenticação
echo Gerando authentication-flow.png...
(
echo sequenceDiagram
echo     autonumber
echo     participant C as Cliente
echo     participant B as BFF
echo     participant K as Kong
echo     participant KC as Keycloak
echo     participant A as API
echo.
echo     C-^>^>B: GET /produtos
echo     B-^>^>KC: Solicita token
echo     KC--^>^>B: Retorna JWT
echo     B-^>^>K: GET /api/produtos + JWT
echo     K-^>^>K: Valida JWT
echo     K-^>^>A: Request autenticado
echo     A--^>^>K: Response
echo     K--^>^>B: Response
echo     B--^>^>C: Dados formatados
) > temp.mmd
mmdc -i temp.mmd -o images/png/authentication-flow.png -t neutral

REM Diagrama 3: Fluxo de Requisições
echo Gerando request-flow.png...
(
echo graph LR
echo     subgraph Cliente
echo         Request[Request /produtos]
echo     end
echo     subgraph BFF
echo         Auth[Verifica Auth]
echo         Token[Obtem Token]
echo         Kong[Tenta Kong]
echo         Direct[Tenta API Direta]
echo         Format[Formata Resposta]
echo     end
echo     Request --^> Auth
echo     Auth --^> Token
echo     Token --^> Kong
echo     Kong --^> Direct
echo     Direct --^> Format
) > temp.mmd
mmdc -i temp.mmd -o images/png/request-flow.png -t neutral

REM Diagrama 4: Validação de Token
echo Gerando token-validation-flow.png...
(
echo sequenceDiagram
echo     participant K as Kong
echo     participant KC as Keycloak
echo     K-^>^>K: Extrai token do header
echo     K-^>^>K: Decodifica JWT
echo     K-^>^>K: Obtem kid
echo     K-^>^>KC: Obtem chave publica
echo     KC--^>^>K: Retorna chave
echo     K-^>^>K: Valida assinatura
echo     K-^>^>K: Verifica claims
) > temp.mmd
mmdc -i temp.mmd -o images/png/token-validation-flow.png -t neutral

REM Diagrama 5: Tratamento de Erros
echo Gerando error-handling-flow.png...
(
echo flowchart TD
echo     A[Request] --^> B{Kong OK?}
echo     B --^>|Sim| C[Via Kong]
echo     B --^>|Nao| D{API OK?}
echo     D --^>|Sim| E[Via API]
echo     D --^>|Nao| F[Mock]
) > temp.mmd
mmdc -i temp.mmd -o images/png/error-handling-flow.png -t neutral

REM Diagrama 6: Fallback
echo Gerando fallback-flow.png...
(
echo stateDiagram-v2
echo     [*] --^> TentandoKong
echo     TentandoKong --^> KongSucesso: OK
echo     TentandoKong --^> TentandoAPI: Falha
echo     TentandoAPI --^> APISucesso: OK
echo     TentandoAPI --^> UsandoMock: Falha
echo     KongSucesso --^> [*]
echo     APISucesso --^> [*]
echo     UsandoMock --^> [*]
) > temp.mmd
mmdc -i temp.mmd -o images/png/fallback-flow.png -t neutral

REM Limpa arquivo temporário
del temp.mmd

echo.
echo === Diagramas gerados com sucesso ===
dir images\png\*.png