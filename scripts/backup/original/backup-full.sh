#!/bin/bash
# backup-full.sh - Backup completo do MySQL com retry e validação

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
DRIVE_FOLDER="${DRIVE_FOLDER:-GestorAlfa/Backups/hourly}"
RETENTION_HOURS="${BACKUP_RETENTION_LOCAL_HOURS:-24}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-Z8K9mN2PqR5sT7uV3wX6yB1cF9gH4jL}"
MAX_RETRIES=3
RETRY_DELAY=10
MIN_SIZE_KB=100

# Detectar ambiente
if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
    DB_CONTAINER="gestor_mysql"
    RCLONE_CONFIG="--config /config/rclone/rclone.conf"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
    DB_CONTAINER="gestor_mysql"
    RCLONE_CONFIG="--config /root/.config/rclone/rclone.conf"
fi

DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="gestor_alfa_${DATE}.sql.gz"
LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "=== Inciando backup full: $FILENAME"

# Função para fazer backup com retry
do_backup() {
    log "Executando mysqldump..."

    # Timeout de 5 minutos para o mysqldump
    timeout 300 docker exec "$DB_CONTAINER" mysqldump -hlocalhost -uroot -p"$MYSQL_ROOT_PASSWORD" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        --routines \
        --triggers \
        --events \
        gestor_alfa 2>/dev/null | gzip > "$BACKUP_DIR/mysql/$FILENAME"

    return $?
}

# Retry loop
RETRY_COUNT=0
while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if do_backup; then
        # Verificar tamanho
        SIZE_KB=$(stat -c%s "$BACKUP_DIR/mysql/$FILENAME" 2>/dev/null || echo 0)

        if [ "$SIZE_KB" -lt $((MIN_SIZE_KB * 1024)) ]; then
            log "AVISO: Backup muito pequeno (${SIZE_KB}KB), esperando e retry..."
            rm -f "$BACKUP_DIR/mysql/$FILENAME"
            RETRY_COUNT=$((RETRY_COUNT + 1))
            sleep $RETRY_DELAY
            continue
        fi

        # Calcula checksum
        sha256sum "$BACKUP_DIR/mysql/$FILENAME" > "$BACKUP_DIR/mysql/$FILENAME.sha256"

        SIZE=$(du -h "$BACKUP_DIR/mysql/$FILENAME" | cut -f1)
        log "Backup criado: $FILENAME ($SIZE)"

        # Sync para Google Drive
        log "Enviando para Google Drive..."
        if rclone $RCLONE_CONFIG copy "$BACKUP_DIR/mysql/$FILENAME" "$DRIVE_REMOTE:GestorAlfa/Backups/hourly" \
            --verbose --transfers 3 --checkers 8 2>&1 | tee -a "$LOG_FILE"; then
            log "Upload concluído para Google Drive"
        else
            log "ERRO: Falha no upload"
            RETRY_COUNT=$((RETRY_COUNT + 1))
            sleep $RETRY_DELAY
            continue
        fi

        # Cleanup local antigo
        find "$BACKUP_DIR/mysql" -name "*.sql.gz" -mmin +$((RETENTION_HOURS * 60)) -delete
        find "$BACKUP_DIR/mysql" -name "*.sha256" -mmin +$((RETENTION_HOURS * 60)) -delete

        log "=== Backup completo finalizado"
        exit 0
    else
        RETRY_COUNT=$((RETRY_COUNT + 1))
        log "ERRO: Tentativa $RETRY_COUNT/$MAX_RETRIES falhou"
        if [ $RETRY_COUNT -lt $MAX_RETRIES ]; then
            log "Retry em $RETRY_DELAY segundos..."
            sleep $RETRY_DELAY
        fi
    fi
done

log "ERRO: Falha após $MAX_RETRIES tentativas"
exit 1
