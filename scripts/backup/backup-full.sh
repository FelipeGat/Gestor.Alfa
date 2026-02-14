#!/bin/sh
# backup-full.sh - Backup completo do MySQL com validação robusta

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
DRIVE_FOLDER="${DRIVE_FOLDER:-GestorAlfa/Backups/hourly}"
RETENTION_HOURS="${BACKUP_RETENTION_LOCAL_HOURS:-24}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-Z8K9mN2PqR5sT7uV3wX6yB1cF9gH4jL}"
MAX_RETRIES=3
RETRY_DELAY=10
MIN_SIZE_KB=100
MIN_LINES=50

if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
    RCLONE_CONFIG_PATH="/config/rclone/rclone.conf"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
    RCLONE_CONFIG_PATH="/root/.config/rclone/rclone.conf"
fi

USE_DOCKER=false
DB_CONTAINER="gestor_mysql"

find_rclone_config() {
    if [ -n "$RCLONE_CONFIG_PATH" ] && [ -f "$RCLONE_CONFIG_PATH" ]; then
        echo "$RCLONE_CONFIG_PATH"
        return 0
    fi
    if [ -f "/config/rclone/rclone.conf" ]; then
        echo "/config/rclone/rclone.conf"
        return 0
    fi
    if [ -f "/root/.config/rclone/rclone.conf" ]; then
        echo "/root/.config/rclone/rclone.conf"
        return 0
    fi
    if [ -f "$HOME/.config/rclone/rclone.conf" ]; then
        echo "$HOME/.config/rclone/rclone.conf"
        return 0
    fi
    return 1
}

RCLONE_CONFIG_FILE=$(find_rclone_config)
if [ -n "$RCLONE_CONFIG_FILE" ]; then
    RCLONE_CONFIG="--config $RCLONE_CONFIG_FILE"
else
    RCLONE_CONFIG=""
    echo "AVISO: Arquivo de configuração do rclone não encontrado"
fi

mkdir -p "$BACKUP_DIR/mysql"

DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="gestor_alfa_${DATE}.sql.gz"
LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "=== Iniciando backup full: $FILENAME"

validate_backup() {
    file="$1"
    size_kb=$2

    min_size=$((MIN_SIZE_KB * 1024))
    if [ "$size_kb" -lt "$min_size" ]; then
        log "ERRO: Backup muito pequeno (${size_kb}KB < ${MIN_SIZE_KB}KB)"
        return 1
    fi

    if ! gunzip -t "$file" 2>/dev/null; then
        log "ERRO: Arquivo não é um gzip válido"
        return 1
    fi

    lines=$(gunzip -c "$file" | wc -l)
    if [ "$lines" -lt "$MIN_LINES" ]; then
        log "ERRO: Backup com poucas linhas ($lines < $MIN_LINES)"
        return 1
    fi

    has_content=$(gunzip -c "$file" | grep -ci "CREATE TABLE" 2>/dev/null || echo "0")
    if [ "$has_content" -eq 0 ]; then
        log "ERRO: Backup não contém dados do banco (sem CREATE TABLE)"
        return 1
    fi

    return 0
}

do_backup() {
    log "Executando mysqldump..."

    timeout 300 docker exec "$DB_CONTAINER" mysqldump -hlocalhost -uroot -p"$MYSQL_ROOT_PASSWORD" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        gestor_alfa 2>/dev/null | gzip > "$BACKUP_DIR/mysql/$FILENAME"

    return $?
}

RETRY_COUNT=0
while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if do_backup; then
        SIZE_KB=$(stat -c%s "$BACKUP_DIR/mysql/$FILENAME" 2>/dev/null || echo 0)

        if ! validate_backup "$BACKUP_DIR/mysql/$FILENAME" "$SIZE_KB"; then
            log "AVISO: Backup inválido, removendo e refazendo..."
            rm -f "$BACKUP_DIR/mysql/$FILENAME"
            RETRY_COUNT=$((RETRY_COUNT + 1))
            sleep $RETRY_DELAY
            continue
        fi

        if command -v sha256sum >/dev/null 2>&1; then
            sha256sum "$BACKUP_DIR/mysql/$FILENAME" > "$BACKUP_DIR/mysql/$FILENAME.sha256"
        fi

        SIZE=$(du -h "$BACKUP_DIR/mysql/$FILENAME" | cut -f1)
        LINES=$(gunzip -c "$BACKUP_DIR/mysql/$FILENAME" | wc -l)
        log "Backup criado: $FILENAME ($SIZE, $LINES linhas)"

        log "Enviando para Google Drive..."
        if [ -n "$RCLONE_CONFIG" ]; then
            if rclone $RCLONE_CONFIG copy "$BACKUP_DIR/mysql/$FILENAME" "$DRIVE_REMOTE:GestorAlfa/Backups/hourly" \
                --verbose --transfers 3 --checkers 8 2>&1 | tee -a "$LOG_FILE"; then
                log "Upload concluído para Google Drive"
            else
                log "ERRO: Falha no upload para Google Drive"
                RETRY_COUNT=$((RETRY_COUNT + 1))
                sleep $RETRY_DELAY
                continue
            fi
        else
            log "ERRO: Não foi possível enviar para Google Drive (sem configuração)"
            RETRY_COUNT=$((RETRY_COUNT + 1))
            sleep $RETRY_DELAY
            continue
        fi

        retention_minutes=$((RETENTION_HOURS * 60))
        find "$BACKUP_DIR/mysql" -name "*.sql.gz" -mmin +$retention_minutes -delete 2>/dev/null || true
        find "$BACKUP_DIR/mysql" -name "*.sha256" -mmin +$retention_minutes -delete 2>/dev/null || true

        log "=== Backup completo finalizado com sucesso"
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
