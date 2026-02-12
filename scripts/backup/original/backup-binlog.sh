#!/bin/bash
# backup-binlog.sh - Copia binary logs a cada 15 minutos

set -e

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
DRIVE_FOLDER="${DRIVE_FOLDER:-GestorAlfa/Backups/binlogs}"

# Detectar se está rodando no container backup ou no host
if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
    MYSQL_DATA_DIR="/var/lib/mysql"
    RCLONE_CONFIG="--config /config/rclone/rclone.conf"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
    MYSQL_DATA_DIR="/root/Gestor.Alfa/data/mysql"
    RCLONE_CONFIG="--config /root/.config/rclone/rclone.conf"
fi

LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [BINLOG] $1" | tee -a "$LOG_FILE"
}

log "Sincronizando binary logs..."

# Verifica se binary logs existem
if [ ! -d "$MYSQL_DATA_DIR" ]; then
    log "Diretório MySQL não encontrado: $MYSQL_DATA_DIR"
    exit 1
fi

BINLOG_COUNT=$(find "$MYSQL_DATA_DIR" -name "mysql-bin.*" -type f 2>/dev/null | wc -l)

if [ "$BINLOG_COUNT" -eq 0 ]; then
    log "Nenhum binary log encontrado"
    exit 0
fi

# Copia cada binlog diretamente do volume
for binlog in $(find "$MYSQL_DATA_DIR" -name "mysql-bin.*" -type f 2>/dev/null); do
    filename=$(basename "$binlog")
    cp "$binlog" "$BACKUP_DIR/binlogs/$filename" 2>/dev/null || true
done

# Copia index
if [ -f "$MYSQL_DATA_DIR/mysql-bin.index" ]; then
    cp "$MYSQL_DATA_DIR/mysql-bin.index" "$BACKUP_DIR/binlogs/" 2>/dev/null || true
fi

# Conta arquivos copiados
COPIED=$(ls -1 "$BACKUP_DIR/binlogs"/mysql-bin.* 2>/dev/null | wc -l)
log "$COPIED binary logs copiados"

# Sync incremental para Drive
rclone $RCLONE_CONFIG sync "$BACKUP_DIR/binlogs" "$DRIVE_REMOTE:GestorAlfa/Backups/binlogs" \
    --verbose --fast-list --ignore-size 2>&1 | tee -a "$LOG_FILE"

log "Binary logs sincronizados"

# Cleanup (mantém 7 dias local)
find "$BACKUP_DIR/binlogs" -name "mysql-bin.*" -mmin +$((7 * 24 * 60)) -delete

log "Cleanup local realizado"
