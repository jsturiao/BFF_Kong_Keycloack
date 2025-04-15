# Lições Aprendidas - Configuração da API

## Princípios Estabelecidos

1. Manter Configurações Simples
   - Usar um único arquivo de configuração quando possível
   - Evitar camadas sobrepostas de configuração
   - Preferir configurações diretas no VirtualHost

2. Apache e PHP
   - Usar configurações padrão do PHP handler
   - Centralizar regras de rewrite no VirtualHost
   - Remover .htaccess em favor de configuração central

3. Logs e Debug
   - Manter logs detalhados durante desenvolvimento
   - Configurar logs de rewrite para debug
   - Incluir informações de debug nas respostas JSON

4. Segurança e CORS
   - Configurar CORS no nível do Apache
   - Usar Kong para validação de JWT
   - Manter Keycloak como autoridade de autenticação

## Padrões a Seguir

1. Estrutura de Arquivos
   ```
   api/
   ├── Dockerfile
   ├── 000-default.conf
   └── public/
       └── index.php
   ```

2. Ordem de Configuração
   - Primeiro: Apache básico
   - Segundo: PHP handler
   - Terceiro: Rewrite rules
   - Quarto: CORS e segurança
