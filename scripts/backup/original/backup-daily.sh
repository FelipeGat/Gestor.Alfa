#!/bin/bash
# backup-daily.sh - Backup diário consolidado

set -e

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
DRIVE_FOLDER="${DRIVE_FOLDER:-GestorAlfa/Backups/daily}"

# Detectar se está rodando no container ou no host
if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
    RCLONE_CONFIG="--config /config/rclone/rclone.conf"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
    RCLONE_CONFIG="--config /root/.config/rclone/rclone.conf"
fi

LOG_FILE="${BACKUP_DIR}/backup.log"

DATE=$(date +%Y%m%d)
FILENAME="gestor_alfa_daily_${DATE}.sql.gz"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [DAILY] $1" | tee -a "$LOG_FILE"
}

log "Criando backup diário: $FILENAME"

# Usa o backup mais recente como base
LATEST=$(ls -t "$BACKUP_DIR"/mysql/*.sql.gz 2>/dev/null | head -1)

if [ -z "$LATEST" ]; then
    log "Nenhum backup encontrado para consolidar"
    exit 1
fi

# Copia para daily
cp "$LATEST" "$BACKUP_DIR/mysql/$FILENAME"

# Sync para Google Drive
rclone $RCLONE_CONFIG copy "$BACKUP_DIR/mysql/$FILENAME" "$DRIVE_REMOTE:GestorAlfa/Backups/daily" \
    --verbose --transfers 3 2>&1 | tee -a "$LOG_FILE"

log "Backup diário enviado para Google Drive"
