# Projeto: Arquitetura PHP + Kong + Keycloak + BFF

Este projeto demonstra a viabilidade de uma arquitetura composta por:

- **API em PHP OO/MVC** (respostas mock)
- **Backend for Frontend (BFF) em PHP**
- **Kong como API Gateway** com autenticação JWT
- **Keycloak como provedor de identidade (SSO + OAuth2)**
- **Ambiente totalmente orquestrado via Docker Compose**

---

## ⚙️ Execução do projeto

> Requisitos: Docker e Docker Compose instalados

```bash
# Clone o projeto
cd app-root

# Suba todos os serviços
docker-compose up -d --build

# Acesse os serviços nos navegadores:
# - BFF: http://localhost:8080
# - API (via Kong): http://localhost:8000/api/produtos (requer JWT)
# - Keycloak: http://localhost:8082
# - Kong Admin: http://localhost:8001
```

---

## 📆 Etapas de Implementação

O projeto é dividido em **8 fases sequenciais**, controladas pelos arquivos em `/roo-instructions`:

| Fase | Descrição | Status |
|------|------------|--------|
| 01   | Estrutura inicial + Docker Compose | ✅ |
| 02   | Configuração do Keycloak e JWT       | ✅ |
| 03   | Configuração do Kong + proteção JWT    | ✅ |
| 04   | API PHP com estrutura MVC e mocks  | ✅ |
| 05   | BFF PHP com requisições via Kong     | ✅ |
| 06   | Login OAuth2 + sessão JWT           | ✅ |
| 07   | Interface Bootstrap + navegação      | ✅ |
| 08   | Testes finais de integração         | ✅ |

Veja os arquivos `.clinerules` dentro da pasta `/roo-instructions` para instruções detalhadas por fase.

---

## 🔹 Integração com o Roo Code

Para utilizar o **Roo Code** na implementação:

1. **Suba apenas o arquivo da fase atual**, por exemplo:
   ```
   roo-instructions/fase-03-kong.clinerules
   ```
2. Aguarde a implementação.
3. Valide os critérios listados.
4. Atualize o `control.clinecheckpoint` com o status `✅ CONCLUÍDO`
5. Siga para a próxima fase.

> Dica: não envie todos os arquivos juntos para o Roo. Trabalhe **uma fase por vez**.

---

## 🔒 Autenticação com Keycloak

- Realm: `app-demo`
- Client: `frontend-bff` (confidential)
- Usuários:
  - admin / 123 (role: admin)
  - usuario / 123 (role: user)

---

## 📈 Roteamento via Kong

- Rota da API: `http://localhost:8000/api/*`
- JWT requerido: emitido pelo Keycloak (algoritmo RS256)
- O Kong protege os endpoints da API com plugin JWT

---

## 🌟 Próximos passos sugeridos

- [ ] Adicionar banco de dados e persistência real
- [ ] Middleware de validação JWT dentro da API
- [ ] Integração com Grafana e Prometheus para métricas
- [ ] Testes automatizados com Postman ou PHPUnit

---

## 📃 Licença
Este projeto é demonstrativo e livre para uso e modificação.

