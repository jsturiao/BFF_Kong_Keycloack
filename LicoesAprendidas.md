# Lições Aprendidas

## 1. Manutenção de Estilos CSS

### ⚠️ IMPORTANTE: Não alterar estilos existentes!

Os seguintes arquivos CSS são considerados ESTÁVEIS e NÃO DEVEM ser modificados:

```
bff/public/assets/css/
├── auth-monitor.css     # Estilos do fluxo de autenticação
├── component-details.css # Estilos dos componentes
└── tabs.css            # Estilos das abas
```

### Razões para não modificar:
1. Os estilos já foram testados e validados
2. Alterações podem quebrar o layout existente
3. Mudanças afetam múltiplos componentes interligados

### Procedimento correto para mudanças de estilo:
1. Criar novo arquivo CSS para novas funcionalidades
2. Usar prefixos específicos para novos estilos
3. Documentar qualquer mudança necessária
4. Obter aprovação antes de modificar estilos existentes

### Componentes sensíveis:
- Fluxo de autenticação (diagrama)
- Estados dos componentes (processando, sucesso, erro)
- Animações e transições
- Layout responsivo

## 2. Problemas Conhecidos

Alterações no CSS podem causar:
- Perda de estados visuais
- Quebra de animações
- Inconsistência no layout
- Comportamentos inesperados em componentes interativos

## 3. Boas Práticas

Para novas funcionalidades:
1. Sempre criar arquivos CSS separados
2. Usar nomenclatura específica para classes
3. Evitar sobrescrever estilos globais
4. Documentar dependências de estilo
