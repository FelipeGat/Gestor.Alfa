#!/bin/bash
# cleanup-drive.sh - Mantém política de retenção no Google Drive

set -e

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"

# Detectar se está rodando no container ou no host
if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
    RCLONE_CONFIG="--config /config/rclone/rclone.conf"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
    RCLONE_CONFIG="--config /root/.config/rclone/rclone.conf"
fi

LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [CLEANUP] $1" | tee -a "$LOG_FILE"
}

log "Iniciando cleanup no Google Drive..."

# Cleanup hourly (mantém 24h = 1 dia)
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/hourly" \
    --min-age 1d 2>&1 | tee -a "$LOG_FILE"
log "Cleanup hourly: arquivos mais antigos que 1 dia removidos"

# Cleanup daily (mantém 7 dias)
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/daily" \
    --min-age 7d 2>&1 | tee -a "$LOG_FILE"
log "Cleanup daily: arquivos mais antigos que 7 dias removidos"

# Cleanup weekly (mantém 4 semanas)
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/weekly" \
    --min-age 4w 2>&1 | tee -a "$LOG_FILE"
log "Cleanup weekly: arquivos mais antigos que 4 semanas removidos"

# Cleanup binlogs (mantém 7 dias)
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/binlogs" \
    --min-age 7d 2>&1 | tee -a "$LOG_FILE"
log "Cleanup binlogs: arquivos mais antigos que 7 dias removidos"

log "Cleanup completo!"
