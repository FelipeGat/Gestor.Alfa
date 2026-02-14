#!/bin/sh
# backup-daily.sh - Backup diario consolidado

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
DRIVE_FOLDER="${DRIVE_FOLDER:-GestorAlfa/Backups/daily}"
LOCK_FILE="/tmp/backup-daily.lock"

if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
fi

find_rclone_config() {
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
fi

if [ -f "$LOCK_FILE" ]; then
    PID=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
    if [ -n "$PID" ]; then
        kill -0 "$PID" 2>/dev/null && exit 0
    fi
    rm -f "$LOCK_FILE"
fi
echo $$ > "$LOCK_FILE"
trap "rm -f $LOCK_FILE" EXIT INT TERM

mkdir -p "$BACKUP_DIR/mysql"

LOG_FILE="${BACKUP_DIR}/backup.log"

DATE=$(date +%Y%m%d)
FILENAME="gestor_alfa_daily_${DATE}.sql.gz"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [DAILY] $1" | tee -a "$LOG_FILE"
}

log "Criando backup diario: $FILENAME"

LATEST=$(ls -t "$BACKUP_DIR"/mysql/*.sql.gz 2>/dev/null | head -1)

if [ -z "$LATEST" ]; then
    log "Nenhum backup encontrado para consolidar"
    exit 1
fi

log "Usando backup base: $LATEST"

if ! gunzip -t "$LATEST" 2>/dev/null; then
    log "ERRO: Backup base invalido"
    exit 1
fi

cp "$LATEST" "$BACKUP_DIR/mysql/$FILENAME"

SIZE=$(du -h "$BACKUP_DIR/mysql/$FILENAME" | cut -f1)
log "Backup diario criado: $FILENAME ($SIZE)"

if [ -n "$RCLONE_CONFIG" ]; then
    if rclone $RCLONE_CONFIG copy "$BACKUP_DIR/mysql/$FILENAME" "$DRIVE_REMOTE:GestorAlfa/Backups/daily" \
        --verbose --transfers 3 2>&1 | tee -a "$LOG_FILE"; then
        log "Backup diario enviado para Google Drive com sucesso"
    else
        log "ERRO: Falha no envio para Google Drive"
        exit 1
    fi
else
    log "ERRO: Configuracao do rclone nao encontrada"
    exit 1
fi

log "Backup diario finalizado"
