#!/bin/sh
# scheduler.sh - Orquestrador de backups robusto (loop continuo)

SCRIPT_DIR="${SCRIPT_DIR:-/scripts}"
if [ ! -d "$SCRIPT_DIR" ]; then
    SCRIPT_DIR="/root/Gestor.Alfa/scripts/backup"
fi

BACKUP_DIR="${BACKUP_DIR:-/backups}"
if [ ! -d "$BACKUP_DIR" ]; then
    BACKUP_DIR="/root/Gestor.Alfa/backups"
fi

LOG_FILE="${BACKUP_DIR}/backup.log"
LOCK_FILE="/tmp/backup-scheduler.lock"
MY_PID=$$

log_msg() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [SCHEDULER] $1" | tee -a "$LOG_FILE"
}

# Verificar lock - apenas verificar se e nosso proprio processo
if [ -f "$LOCK_FILE" ]; then
    LOCK_PID=$(cat "$LOCK_FILE" 2>/dev/null)
    if [ -n "$LOCK_PID" ] && [ "$LOCK_PID" != "$MY_PID" ]; then
        # Verificar se o processo ainda esta rodando
        kill -0 "$LOCK_PID" 2>/dev/null
        if [ $? -eq 0 ]; then
            exit 0
        fi
    fi
fi

# Criar lock
echo "$MY_PID" > "$LOCK_FILE"

log_msg "Iniciando scheduler de backups (loop continuo)..."

run_script() {
    script_name="$1"

    if [ ! -f "$SCRIPT_DIR/$script_name" ]; then
        log_msg "ERRO: Script nao encontrado: $SCRIPT_DIR/$script_name"
        return 1
    fi

    log_msg "Executando $script_name..."
    sh "$SCRIPT_DIR/$script_name" >> "$LOG_FILE" 2>&1
    result=$?
    if [ $result -ne 0 ]; then
        log_msg "ERRO: $script_name falhou (codigo: $result)"
        return 1
    fi
    return 0
}

while true; do
    MINUTE=$(date +%M)
    HOUR=$(date +%H)
    DAY=$(date +%u)

    # Hourly: backup full a cada hora no minuto 00
    if [ "$MINUTE" = "00" ]; then
        run_script "backup-full.sh"
    fi

    # Binlog: a cada 15 minutos (00, 15, 30, 45)
    if [ "$MINUTE" = "00" ] || [ "$MINUTE" = "15" ] || [ "$MINUTE" = "30" ] || [ "$MINUTE" = "45" ]; then
        run_script "backup-binlog.sh"
    fi

    # Daily: todo dia as 2h
    if [ "$MINUTE" = "00" ] && [ "$HOUR" = "02" ]; then
        run_script "backup-daily.sh"
    fi

    # Weekly: Domingo as 3h
    if [ "$MINUTE" = "00" ] && [ "$HOUR" = "03" ] && [ "$DAY" = "7" ]; then
        run_script "backup-daily.sh"
        run_script "cleanup-drive.sh"
    fi

    # Cleanup Drive: Diario as 4h
    if [ "$MINUTE" = "00" ] && [ "$HOUR" = "04" ]; then
        run_script "cleanup-drive.sh"
    fi

    # Test Restore: Domingo as 4h
    if [ "$MINUTE" = "00" ] && [ "$HOUR" = "04" ] && [ "$DAY" = "7" ]; then
        run_script "test-restore.sh"
    fi

    sleep 60
done
