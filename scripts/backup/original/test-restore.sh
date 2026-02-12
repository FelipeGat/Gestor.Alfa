#!/bin/bash
# test-restore.sh - Testa restauração em banco temporário (Domingo 4h)

set -e

TEST_DB="gestor_alfa_test_restore"
EMAIL_TO="${BACKUP_ALERT_EMAIL:-}"

# Detectar se está rodando no container ou no host
if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
fi

LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [TEST] $1" | tee -a "$LOG_FILE"
}

log "=== Iniciando teste de restauração ==="

# Encontra o backup mais recente
LATEST=$(ls -t "$BACKUP_DIR"/mysql/*.sql.gz 2>/dev/null | head -1)

if [ -z "$LATEST" ]; then
    log "ERRO: Nenhum backup encontrado"
    exit 1
fi

log "Usando backup: $LATEST"

# Remove container de teste anterior se existir
docker rm -f "$TEST_DB" 2>/dev/null || true

# Cria container temporário MySQL
log "Criando container temporário para teste..."
docker run -d --name "$TEST_DB" \
    -e MYSQL_ROOT_PASSWORD=test_root \
    -e MYSQL_DATABASE="$TEST_DB" \
    mysql:8.0 2>&1 | tee -a "$LOG_FILE"

# Espera MySQL estar pronto
sleep 30

# Verifica se MySQL iniciou
if ! docker exec "$TEST_DB" mysqladmin ping -uroot -ptest_root 2>/dev/null; then
    log "ERRO: Falha ao iniciar MySQL de teste"
    docker rm -f "$TEST_DB" 2>/dev/null
    exit 1
fi

# Cria banco de teste
docker exec "$TEST_DB" mysql -uroot -ptest_root -e "CREATE DATABASE IF NOT EXISTS $TEST_DB" 2>/dev/null

# Restaura backup
log "Restaurando backup..."
docker exec -i "$TEST_DB" mysql -uroot -ptest_root "$TEST_DB" < <(gunzip -c "$LATEST") 2>&1 | tee -a "$LOG_FILE"

if [ $? -ne 0 ]; then
    log "ERRO: Falha na restauração"
    docker rm -f "$TEST_DB"
    exit 1
fi

# Verifica integridade
TABLES=$(docker exec "$TEST_DB" mysql -uroot -ptest_root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TEST_DB'" 2>/dev/null)

if [ -z "$TABLES" ] || [ "$TABLES" -eq 0 ]; then
    log "ERRO: Nenhuma tabela restaurada - BACKUP CORROMPIDO!"
    docker rm -f "$TEST_DB"
    exit 1
fi

# Verifica algumas tabelas críticas
CRITICAL_TABLES=$(docker exec "$TEST_DB" mysql -uroot -ptest_root -N -e "
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema='$TEST_DB'
    AND table_name IN ('users', 'contracts', 'invoices', 'settings')
" 2>/dev/null)

log "Tabelas restauradas: $TABLES"
log "Tabelas críticas encontradas: $CRITICAL_TABLES"

# Cleanup
docker stop "$TEST_DB" && docker rm "$TEST_DB"

if [ "$CRITICAL_TABLES" -ge 3 ]; then
    log "✅ TESTE PASSOU: Backup restaurado com sucesso!"
    exit 0
else
    log "⚠️ AVISO: Backup restaurado mas faltan tabelas críticas"
    exit 1
fi
