#!/bin/sh
# test-restore.sh - Testa restauração em banco temporário (Domingo 4h)

set -e

TEST_DB="gestor_alfa_test_restore"
EMAIL_TO="${BACKUP_ALERT_EMAIL:-}"

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

LATEST=$(ls -t "$BACKUP_DIR"/mysql/*.sql.gz 2>/dev/null | head -1)

if [ -z "$LATEST" ]; then
    log "ERRO: Nenhum backup encontrado"
    exit 1
fi

log "Usando backup: $LATEST"

docker rm -f "$TEST_DB" 2>/dev/null || true

log "Criando container temporário para teste..."
docker run -d --name "$TEST_DB" \
    -e MYSQL_ROOT_PASSWORD=test_root \
    -e MYSQL_DATABASE="$TEST_DB" \
    mysql:8.0 2>&1 | tee -a "$LOG_FILE"

sleep 30

if ! docker exec "$TEST_DB" mysqladmin ping -uroot -ptest_root 2>/dev/null; then
    log "ERRO: Falha ao iniciar MySQL de teste"
    docker rm -f "$TEST_DB" 2>/dev/null
    exit 1
fi

docker exec "$TEST_DB" mysql -uroot -ptest_root -e "CREATE DATABASE IF NOT EXISTS $TEST_DB" 2>/dev/null

log "Restaurando backup..."
TEMP_FILE="/tmp/restore_test.sql.gz"
cp "$LATEST" "$TEMP_FILE"
gunzip -c "$TEMP_FILE" 2>/dev/null | docker exec -i "$TEST_DB" mysql --silent -uroot -ptest_root "$TEST_DB"
RESULT=$?
rm -f "$TEMP_FILE"

if [ $RESULT -ne 0 ]; then
    log "ERRO: Falha na restauração"
    docker rm -f "$TEST_DB"
    exit 1
fi

TABLES=$(docker exec "$TEST_DB" mysql -uroot -ptest_root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TEST_DB'" 2>/dev/null)

if [ -z "$TABLES" ] || [ "$TABLES" -eq 0 ]; then
    log "ERRO: Nenhuma tabela restaurada - BACKUP CORROMPIDO!"
    docker rm -f "$TEST_DB"
    exit 1
fi

CRITICAL_TABLES=$(docker exec "$TEST_DB" mysql -uroot -ptest_root -N -e "
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema='$TEST_DB'
    AND table_name IN ('clientes', 'cobranca', 'atendimentos', 'contas', 'assuntos')
" 2>/dev/null)

log "Tabelas restauradas: $TABLES"
log "Tabelas críticas encontradas: $CRITICAL_TABLES"

docker stop "$TEST_DB" && docker rm "$TEST_DB"

if [ "$CRITICAL_TABLES" -ge 3 ]; then
    log "TESTE PASSOU: Backup restaurado com sucesso!"
    exit 0
else
    log "AVISO: Backup restaurado mas faltan tabelas críticas"
    exit 1
fi
