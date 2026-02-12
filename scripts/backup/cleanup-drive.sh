#!/bin/bash
# cleanup-drive.sh - Mantem politica de retencao no Google Drive

set -euo pipefail

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
LOCK_FILE="/tmp/backup-cleanup.lock"

# Detectar ambiente
if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
fi

# Tentar localizar configuracao do rclone em multiplos locais
find_rclone_config() {
    local paths=(
        "/config/rclone/rclone.conf"
        "/root/.config/rclone/rclone.conf"
        "$HOME/.config/rclone/rclone.conf"
    )

    for path in "${paths[@]}"; do
        if [ -n "$path" ] && [ -f "$path" ]; then
            echo "$path"
            return 0
        fi
    done
    return 1
}

RCLONE_CONFIG_FILE=$(find_rclone_config)
if [ -n "$RCLONE_CONFIG_FILE" ]; then
    RCLONE_CONFIG="--config $RCLONE_CONFIG_FILE"
else
    RCLONE_CONFIG=""
fi

# Verificar lock
if [ -f "$LOCK_FILE" ]; then
    PID=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
    if [ -n "$PID" ] && kill -0 "$PID" 2>/dev/null; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] [CLEANUP] Ja em execucao (PID: $PID), ignorando..."
        exit 0
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

# Cleanup hourly (mantem 24h = 1 dia)
log "Limpando hourly (mais antigos que 1 dia)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/hourly" \
    --min-age 1d 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup hourly concluído"

# Cleanup daily (mantem 7 dias)
log "Limpando daily (mais antigos que 7 dias)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/daily" \
    --min-age 7d 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup daily concluído"

# Cleanup weekly (mantem 4 semanas)
log "Limpando weekly (mais antigos que 4 semanas)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/weekly" \
    --min-age 4w 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup weekly concluído"

# Cleanup binlogs (mantem 7 dias)
log "Limpando binlogs (mais antigos que 7 dias)..."
rclone $RCLONE_CONFIG delete "$DRIVE_REMOTE:GestorAlfa/Backups/binlogs" \
    --min-age 7d 2>&1 | tee -a "$LOG_FILE" || true
log "Cleanup binlogs concluído"

log "Cleanup completo!"
