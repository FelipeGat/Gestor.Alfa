#!/bin/bash
# Script de rollback rápido - apenasigitando ./r
# ==========================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

VPS_HOST="76.13.233.13"
VPS_USER="root"
VPS_PATH="/var/www"
CONTAINER_PHP="gestor_php"

echo -e "${YELLOW}🔄 Fazendo rollback na VPS...${NC}"

ssh -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_HOST} "
    cd ${VPS_PATH}
    git reset --hard HEAD~1
    git push --force origin main
    docker exec ${CONTAINER_PHP} git -C ${VPS_PATH} pull origin main
    docker exec ${CONTAINER_PHP} php ${VPS_PATH}/artisan config:clear
    docker exec ${CONTAINER_PHP} php ${VPS_PATH}/artisan view:clear
    docker exec ${CONTAINER_PHP} php ${VPS_PATH}/artisan cache:clear
    echo -e '${GREEN}✅ Rollback concluído!${NC}'
"
