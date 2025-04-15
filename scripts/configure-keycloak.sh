#!/bin/bash

# Configuração do Keycloak via API REST
echo "Iniciando configuração do Keycloak..."

# Função para aguardar o Keycloak estar pronto
wait_for_keycloak() {
    echo "Aguardando Keycloak inicializar..."
    while ! curl -s http://localhost:8082 > /dev/null; do
        sleep 5
    done
    echo "Keycloak está pronto!"
}

wait_for_keycloak

# Obtém token de admin
echo "Obtendo token de admin..."
TOKEN=$(curl -s -d "client_id=admin-cli" -d "username=admin" -d "password=admin" -d "grant_type=password" "http://localhost:8082/realms/master/protocol/openid-connect/token" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "Erro: Não foi possível obter o token de admin"
    exit 1
fi

# 0. Remover realm existente se houver
echo "Removendo realm app-demo existente..."
curl -s -X DELETE "http://localhost:8082/admin/realms/app-demo" \
-H "Authorization: Bearer $TOKEN"

sleep 5

# 1. Criar Realm
echo "Criando realm app-demo..."
curl -s -X POST "http://localhost:8082/admin/realms" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "realm": "app-demo",
  "enabled": true,
  "displayName": "App Demo",
  "sslRequired": "external",
  "registrationAllowed": false
}'

sleep 5

# 2. Criar Client
echo "Criando client frontend-bff..."
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/clients" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "clientId": "frontend-bff",
  "enabled": true,
  "protocol": "openid-connect",
  "publicClient": false,
  "clientAuthenticatorType": "client-secret",
  "secret": "frontend-bff-secret",
  "standardFlowEnabled": true,
  "directAccessGrantsEnabled": true,
  "serviceAccountsEnabled": true,
  "redirectUris": [
    "http://localhost:8080/callback",
    "http://bff/callback"
  ],
  "webOrigins": ["http://localhost:8080"],
  "attributes": {
    "pkce.code.challenge.method": "S256"
  }
}'

sleep 5

# Obter client ID
echo "Obtendo client ID..."
CLIENT_ID=$(curl -s -X GET "http://localhost:8082/admin/realms/app-demo/clients" \
-H "Authorization: Bearer $TOKEN" | grep -o '"id":"[^"]*' | cut -d'"' -f4 | head -n1)

if [ -z "$CLIENT_ID" ]; then
    echo "Erro: Não foi possível obter o client ID"
    exit 1
fi

# 3. Criar Roles do Realm
echo "Criando roles..."
# Role: user
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/roles" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "name": "user",
  "description": "Regular user role"
}'

sleep 2

# Role: admin
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/roles" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "name": "admin",
  "description": "Administrator role"
}'

sleep 5

# 4. Criar Usuários
echo "Criando usuários..."
# Usuário: admin
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/users" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "username": "admin",
  "enabled": true,
  "emailVerified": true,
  "credentials": [{
    "type": "password",
    "value": "123",
    "temporary": false
  }]
}'

sleep 5

# Obter admin role ID
ADMIN_ROLE_ID=$(curl -s -X GET "http://localhost:8082/admin/realms/app-demo/roles" \
-H "Authorization: Bearer $TOKEN" | grep -o '"name":"admin","id":"[^"]*' | cut -d'"' -f6)

# Obter user role ID
USER_ROLE_ID=$(curl -s -X GET "http://localhost:8082/admin/realms/app-demo/roles" \
-H "Authorization: Bearer $TOKEN" | grep -o '"name":"user","id":"[^"]*' | cut -d'"' -f6)

# Obter ID do usuário admin
ADMIN_ID=$(curl -s -X GET "http://localhost:8082/admin/realms/app-demo/users" \
-H "Authorization: Bearer $TOKEN" | grep -o '"username":"admin","id":"[^"]*' | cut -d'"' -f6)

# Atribuir role admin ao usuário admin
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/users/$ADMIN_ID/role-mappings/realm" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '[{
  "id": "'$ADMIN_ROLE_ID'",
  "name": "admin"
}]'

sleep 2

# Usuário: usuario
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/users" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "username": "usuario",
  "enabled": true,
  "emailVerified": true,
  "credentials": [{
    "type": "password",
    "value": "123",
    "temporary": false
  }]
}'

sleep 5

# Obter ID do usuário comum
USER_ID=$(curl -s -X GET "http://localhost:8082/admin/realms/app-demo/users" \
-H "Authorization: Bearer $TOKEN" | grep -o '"username":"usuario","id":"[^"]*' | cut -d'"' -f6)

# Atribuir role user ao usuário comum
curl -s -X POST "http://localhost:8082/admin/realms/app-demo/users/$USER_ID/role-mappings/realm" \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d '[{
  "id": "'$USER_ROLE_ID'",
  "name": "user"
}]'

echo "Configuração do Keycloak concluída!"

# Validar a configuração testando a geração de token
echo "Testando geração de token..."
curl -s -X POST "http://localhost:8082/realms/app-demo/protocol/openid-connect/token" \
-d "client_id=frontend-bff" \
-d "client_secret=frontend-bff-secret" \
-d "username=admin" \
-d "password=123" \
-d "grant_type=password"

echo -e "\nScript finalizado!"