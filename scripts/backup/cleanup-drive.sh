#!/bin/sh
# cleanup-drive.sh - Mantem politica de retencao no Google Drive

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
LOCK_FILE="/tmp/backup-cleanup.lock"

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

LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [CLEANUP] $1" | tee -a "$LOG_FILE"
}

if [ -z "$RCLONE_CONFIG" ]; then
    log "ERRO: Configuracao do rclone nao encontrada"
    exit 1
fi

log "Iniciando cleanup no Google Drive..."

log "Limpando hourly (mais antigos que 1 dia)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/hourly" \
    --min-age 1d 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup hourly concluído"

log "Limpando daily (mais antigos que 7 dias)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/daily" \
    --min-age 7d 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup daily concluído"

log "Limpando weekly (mais antigos que 4 semanas)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/weekly" \
    --min-age 4w 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup weekly concluído"

log "Limpando binlogs (mais antigos que 7 dias)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/binlogs" \
    --min-age 7d 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup binlogs concluído"

log "Cleanup completo!"
