#!/bin/bash

echo "Iniciando configuração do Kong..."

# Função para aguardar o Kong estar pronto
wait_for_kong() {
    echo "Aguardando Kong inicializar..."
    while ! curl -s http://localhost:8001 > /dev/null; do
        sleep 5
    done
    echo "Kong está pronto!"
}

# Função para aguardar o Keycloak estar pronto
wait_for_keycloak() {
    echo "Aguardando Keycloak inicializar..."
    while ! curl -s http://localhost:8082/realms/app-demo > /dev/null; do
        sleep 5
    done
    echo "Keycloak está pronto!"
}

wait_for_kong
wait_for_keycloak

# 1. Limpar configurações existentes
echo "Limpando configurações existentes..."

echo "Removendo plugins..."
for plugin_id in $(curl -s http://localhost:8001/plugins | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/plugins/$plugin_id
done

echo "Removendo routes..."
for route_id in $(curl -s http://localhost:8001/routes | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/routes/$route_id
done

echo "Removendo services..."
for service_id in $(curl -s http://localhost:8001/services | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/services/$service_id
done

echo "Removendo consumers..."
for consumer_id in $(curl -s http://localhost:8001/consumers | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/consumers/$consumer_id
done

sleep 2

# 2. Criar Serviço
echo "Criando serviço api-service..."
curl -i -X POST http://localhost:8001/services \
    --data name=api-service \
    --data url=http://api:80 \
    --data path=/

sleep 2

# 3. Criar Rota
echo "Criando rota /api..."
curl -i -X POST http://localhost:8001/services/api-service/routes \
    --data 'paths[]=/api' \
    --data 'paths[]=/api/(.*)' \
    --data 'strip_path=false' \
    --data 'preserve_host=false' \
    --data 'protocols[]=http' \
    --data 'protocols[]=https'

sleep 2

# 4. Ativar plugin JWT
echo "Ativando plugin JWT..."
ISSUER="http://localhost:8082/realms/app-demo"

curl -i -X POST http://localhost:8001/services/api-service/plugins \
    --data name=jwt \
    --data "config.key_claim_name=iss" \
    --data "config.claims_to_verify=exp" \
    --data "config.run_on_preflight=true"

sleep 2

# 5. Criar Consumer
echo "Criando consumer frontend-bff..."
curl -i -X POST http://localhost:8001/consumers \
    --data username=frontend-bff \
    --data custom_id="$ISSUER"

sleep 2

# 6. Obter chave pública do Keycloak
echo "Obtendo chave pública do Keycloak..."
REALM_INFO=$(curl -s http://localhost:8082/realms/app-demo)
PUBLIC_KEY=$(echo $REALM_INFO | grep -o '"public_key":"[^"]*' | cut -d'"' -f4)

if [ -z "$PUBLIC_KEY" ]; then
    echo "Erro: Não foi possível obter a chave pública do Keycloak"
    exit 1
fi

# Formatar a chave pública
PUBLIC_KEY_PEM="-----BEGIN PUBLIC KEY-----
$PUBLIC_KEY
-----END PUBLIC KEY-----"

echo "Chave pública obtida:"
echo "$PUBLIC_KEY_PEM"

# 7. Registrar a chave JWT
echo "Registrando chave JWT..."
curl -i -X POST http://localhost:8001/consumers/frontend-bff/jwt \
    --data "algorithm=RS256" \
    --data "key=$ISSUER" \
    --data-urlencode "rsa_public_key=$PUBLIC_KEY_PEM"

echo "Configuração do Kong concluída!"

# 8. Testar endpoints
echo -e "\nTestando endpoints..."

# Obter token
echo "Obtendo token..."
TOKEN_RESPONSE=$(curl -s -X POST "http://localhost:8082/realms/app-demo/protocol/openid-connect/token" \
    -d "client_id=frontend-bff" \
    -d "client_secret=frontend-bff-secret" \
    -d "username=admin" \
    -d "password=123" \
    -d "grant_type=password")

TOKEN=$(echo $TOKEN_RESPONSE | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "Erro: Não foi possível obter o token. Resposta completa:"
    echo $TOKEN_RESPONSE
    exit 1
fi

# 9. Testar vários endpoints
echo -e "\nTestando acesso ao endpoint /api/produtos com JWT..."
curl -i -X GET http://localhost:8000/api/produtos \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json"

echo -e "\nTestando acesso ao endpoint /api com JWT..."
curl -i -X GET http://localhost:8000/api \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json"

echo -e "\nTestando acesso sem JWT (deve retornar 401)..."
curl -i -X GET http://localhost:8000/api/produtos \
    -H "Accept: application/json"

echo -e "\nScript finalizado!"