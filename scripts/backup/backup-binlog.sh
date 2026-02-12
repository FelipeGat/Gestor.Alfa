#!/bin/sh
# backup-binlog.sh - Copia binary logs a cada 15 minutos

DRIVE_REMOTE="${DRIVE_REMOTE:-google_drive}"
LOCK_FILE="/tmp/backup-binlog.lock"

if [ -d "/backups" ]; then
    BACKUP_DIR="/backups"
    MYSQL_DATA_DIR="/var/lib/mysql"
else
    BACKUP_DIR="/root/Gestor.Alfa/backups"
    MYSQL_DATA_DIR="/var/lib/mysql"
    if [ ! -d "$MYSQL_DATA_DIR" ]; then
        MYSQL_DATA_DIR="/root/Gestor.Alfa/data/mysql"
    fi
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

mkdir -p "$BACKUP_DIR/binlogs"

if [ -f "$LOCK_FILE" ]; then
    PID=$(cat "$LOCK_FILE" 2>/dev/null)
    if [ -n "$PID" ]; then
        kill -0 "$PID" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] [BINLOG] Ja em execucao (PID: $PID), ignorando..."
            exit 0
        fi
    fi
    rm -f "$LOCK_FILE"
fi
echo $$ > "$LOCK_FILE"

LOG_FILE="${BACKUP_DIR}/backup.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [BINLOG] $1" | tee -a "$LOG_FILE"
}

log "Sincronizando binary logs..."

if [ ! -d "$MYSQL_DATA_DIR" ]; then
    log "Diretorio MySQL nao encontrado: $MYSQL_DATA_DIR"
    rm -f "$LOCK_FILE"
    exit 0
fi

BINLOG_COUNT=$(find "$MYSQL_DATA_DIR" -name "mysql-bin.*" -type f 2>/dev/null | wc -l)

if [ "$BINLOG_COUNT" -eq 0 ]; then
    log "Nenhum binary log encontrado"
    rm -f "$LOCK_FILE"
    exit 0
fi

log "Encontrados $BINLOG_COUNT binary logs"

for binlog in $(find "$MYSQL_DATA_DIR" -name "mysql-bin.*" -type f 2>/dev/null); do
    filename=$(basename "$binlog")
    cp "$binlog" "$BACKUP_DIR/binlogs/$filename" 2>/dev/null
done

if [ -f "$MYSQL_DATA_DIR/mysql-bin.index" ]; then
    cp "$MYSQL_DATA_DIR/mysql-bin.index" "$BACKUP_DIR/binlogs/" 2>/dev/null
fi

COPIED=$(ls -1 "$BACKUP_DIR/binlogs"/mysql-bin.* 2>/dev/null | wc -l || echo 0)
log "$COPIED binary logs copiados"

if [ -n "$RCLONE_CONFIG" ]; then
    rclone $RCLONE_CONFIG sync "$BACKUP_DIR/binlogs" "$DRIVE_REMOTE:GestorAlfa/Backups/binlogs" \
        --verbose --fast-list --ignore-size 2>&1 | tee -a "$LOG_FILE"
    if [ $? -eq 0 ]; then
        log "Binary logs sincronizados para Google Drive"
    else
        log "ERRO: Falha no sync para Google Drive"
    fi
else
    log "ERRO: Configuracao do rclone nao encontrada"
fi

find "$BACKUP_DIR/binlogs" -name "mysql-bin.*" -mmin +$((7 * 24 * 60)) -delete 2>/dev/null || true

log "Cleanup local realizado"

rm -f "$LOCK_FILE"
exit 0
