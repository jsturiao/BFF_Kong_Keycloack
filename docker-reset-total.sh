#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[1;36m'
YELLOW='\033[1;33m'
BOLD='\033[1m'
NC='\033[0m' # No Color

bar() {
  local i=0
  local max=20
  echo -ne "${CYAN}⏳ Limpando: ["
  while [ $i -le $max ]; do
    echo -ne "#"
    sleep 0.03
    ((i++))
  done
  echo -e "]${NC}"
}

clear
echo -e "${YELLOW}${BOLD}=============================================================="
echo -e "   ⚠️  AVISO: Este script irá REMOVER TODO o ambiente Docker"
echo -e "   Contêineres, imagens, volumes, redes e cache serão apagados"
echo -e "==============================================================${NC}"

read -p "Deseja continuar? (s/n): " confirm
if [[ "$confirm" != "s" ]]; then
  echo -e "${RED}❌ Operação cancelada.${NC}"
  exit 0
fi
echo ""

# ---[ CONTAINERS ]---
echo -e "${CYAN}${BOLD}🔹 [1/5] CONTAINERS${NC}"
containers=$(docker ps -aq)
if [ -n "$containers" ]; then
  echo -e "${YELLOW}→ Contêineres encontrados:${NC}"
  docker ps -a --format "  - {{.ID}} - {{.Image}} ({{.Status}})"

  bar
  docker stop $containers > /dev/null
  docker rm -f $containers > /dev/null
  echo -e "${GREEN}✔ Todos os contêineres foram removidos.${NC}"
else
  echo -e "${YELLOW}ℹ️ Nenhum contêiner encontrado.${NC}"
fi
echo ""

# ---[ IMAGENS ]---
echo -e "${CYAN}${BOLD}🔹 [2/5] IMAGENS${NC}"
images=$(docker images -q)
if [ -n "$images" ]; then
  echo -e "${YELLOW}→ Imagens encontradas:${NC}"
  docker images --format "  - {{.Repository}}:{{.Tag}} ({{.ID}})"

  bar
  docker rmi -f $images > /dev/null
  echo -e "${GREEN}✔ Todas as imagens foram removidas.${NC}"
else
  echo -e "${YELLOW}ℹ️ Nenhuma imagem encontrada.${NC}"
fi
echo ""

# ---[ VOLUMES ]---
echo -e "${CYAN}${BOLD}🔹 [3/5] VOLUMES${NC}"
volumes=$(docker volume ls -q)
if [ -n "$volumes" ]; then
  echo -e "${YELLOW}→ Volumes encontrados:${NC}"
  docker volume ls --format "  - {{.Name}}"

  bar
  docker volume rm -f $volumes > /dev/null
  echo -e "${GREEN}✔ Todos os volumes foram removidos.${NC}"
else
  echo -e "${YELLOW}ℹ️ Nenhum volume encontrado.${NC}"
fi
echo ""

# ---[ REDES ]---
echo -e "${CYAN}${BOLD}🔹 [4/5] REDES PERSONALIZADAS${NC}"
networks=$(docker network ls | grep -v "bridge\|host\|none" | awk '{print $1}' | tail -n +2)
if [ -n "$networks" ]; then
  echo -e "${YELLOW}→ Redes personalizadas encontradas:${NC}"
  docker network ls | grep -v "bridge\|host\|none" | awk '{print "  - "$2}'

  bar
  docker network rm $networks > /dev/null
  echo -e "${GREEN}✔ Redes personalizadas removidas.${NC}"
else
  echo -e "${YELLOW}ℹ️ Nenhuma rede personalizada encontrada.${NC}"
fi
echo ""

# ---[ CACHE BUILDER ]---
echo -e "${CYAN}${BOLD}🔹 [5/5] CACHE DE BUILDS${NC}"
echo -e "${YELLOW}→ Limpando cache de imagens e builders...${NC}"
bar
docker builder prune -af --filter "until=0h" > /dev/null 2>&1
echo -e "${GREEN}✔ Cache de build limpo.${NC}"
echo ""

# ---[ FINALIZAÇÃO ]---
echo -e "${GREEN}${BOLD}✅ Docker completamente limpo e resetado com sucesso.${NC}"
echo -e "${YELLOW}⚠️ Reinicie o Docker se necessário para aplicar todas as alterações.${NC}"
