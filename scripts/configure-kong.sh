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

# 1. Aguardar Kong
wait_for_kong

# 2. Limpar configurações existentes
echo "Limpando configurações existentes..."

echo "Removendo rotas..."
for route_id in $(curl -s http://localhost:8001/routes | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/routes/$route_id
done

echo "Removendo serviços..."
for service_id in $(curl -s http://localhost:8001/services | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/services/$service_id
done

echo "Removendo plugins..."
for plugin_id in $(curl -s http://localhost:8001/plugins | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/plugins/$plugin_id
done

echo "Removendo consumers..."
for consumer_id in $(curl -s http://localhost:8001/consumers | grep -o '"id":"[^"]*' | cut -d'"' -f4); do
    curl -s -X DELETE http://localhost:8001/consumers/$consumer_id
done

sleep 2

# 3. Criar Serviço para API
echo "Criando serviço para API..."
curl -i -X POST http://localhost:8001/services \
    --data "name=api-produtos" \
    --data "url=http://api:80/produtos"

sleep 2

# 4. Criar Rota
echo "Criando rota..."
curl -i -X POST http://localhost:8001/services/api-produtos/routes \
    --data "paths[]=/api/produtos" \
    --data "strip_path=true" \
    --data "preserve_host=false" \
    --data "name=api-produtos-route"

sleep 2

# 5. Obter informações do Keycloak
echo "Obtendo informações do Keycloak..."
KEYCLOAK_URL="http://localhost:8082"
REALM="app-demo"

# Obter certificado público
echo "Obtendo certificado público do Keycloak..."
CERT_RESPONSE=$(curl -s "$KEYCLOAK_URL/realms/$REALM/protocol/openid-connect/certs")
PUBLIC_KEY=$(echo $CERT_RESPONSE | jq -r '.keys[0].x5c[0]' | base64 -d | openssl x509 -pubkey -noout)

if [ -z "$PUBLIC_KEY" ]; then
    echo "Erro: Não foi possível obter a chave pública"
    exit 1
fi

echo "Chave pública obtida:"
echo "$PUBLIC_KEY"

# 6. Criar consumer para o Keycloak
echo "Criando consumer para o Keycloak..."
curl -i -X POST http://localhost:8001/consumers \
    --data "username=keycloak" \
    --data "custom_id=http://localhost:8082/realms/app-demo"

sleep 2

# 7. Adicionar chave pública ao consumer
echo "Configurando credenciais JWT para o consumer..."
curl -i -X POST http://localhost:8001/consumers/keycloak/jwt \
    --data "algorithm=RS256" \
    --data "key=http://localhost:8082/realms/app-demo" \
    --data-urlencode "rsa_public_key=$PUBLIC_KEY"

sleep 2

# 8. Configurar plugin JWT
echo "Configurando plugin JWT..."
curl -i -X POST http://localhost:8001/plugins \
    --data "name=jwt" \
    --data "config.uri_param_names=jwt" \
    --data "config.header_names=Authorization" \
    --data "config.key_claim_name=iss" \
    --data "config.claims_to_verify=exp" \
    --data "config.run_on_preflight=true"

echo -e "\nTestando configuração..."

echo "1. Obtendo token do Keycloak..."
TOKEN=$(curl -s -X POST "$KEYCLOAK_URL/realms/$REALM/protocol/openid-connect/token" \
    -d "client_id=frontend-bff" \
    -d "client_secret=frontend-bff-secret" \
    -d "username=admin" \
    -d "password=123" \
    -d "grant_type=password" | jq -r .access_token)

if [ -n "$TOKEN" ]; then
    echo "Token obtido (${#TOKEN} caracteres)"
    
    echo -e "\n2. Testando rota com token..."
    curl -i -X GET http://localhost:8000/api/produtos \
        -H "Authorization: Bearer $TOKEN" \
        -H "Accept: application/json"
else
    echo "Erro ao obter token"
fi

echo -e "\nConfigurações finais:"
echo "- Kong Admin API: http://localhost:8001"
echo "- Kong Proxy: http://localhost:8000"
echo "- API URL: http://api:80/produtos"
echo "- Keycloak URL: $KEYCLOAK_URL"
echo "- Realm: $REALM"

echo -e "\nScript finalizado!"