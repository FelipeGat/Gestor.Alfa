#!/bin/bash
# scheduler.sh - Orquestrador de backups robusto

SCRIPT_DIR="/scripts"
LOG_FILE="${BACKUP_DIR:-/backups}/backup.log"
LOCK_FILE="/tmp/backup-scheduler.lock"
LAST_RUN_FILE="/tmp/backup-last-run.log"

log_msg() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [SCHEDULER] $1" >> "$LOG_FILE"
}

# Verificar se já está em execução
if [ -f "$LOCK_FILE" ]; then
    PID=$(cat "$LOCK_FILE" 2>/dev/null)
    if [ -n "$PID" ] && kill -0 "$PID" 2>/dev/null; then
        log_msg "Scheduler já em execução (PID: $PID), ignorando..."
        exit 0
    fi
    rm -f "$LOCK_FILE"
fi

# Criar lock
echo $$ > "$LOCK_FILE"

# Capturar minuto e hora
MINUTE=$(date +%M)
HOUR=$(date +%H)
DAY=$(date +%u)
TIMESTAMP=$(date +%Y%m%d_%H%M)

log_msg "Verificando tarefas (M=$MINUTE H=$HOUR)..."

# Hourly: backup full a cada hora no minuto 00
if [ "$MINUTE" = "00" ]; then
    log_msg "Iniciando backup full"
    sh "$SCRIPT_DIR/backup-full.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Backup full falhou"
fi

# Binlog: a cada 15 minutos
if [ "$MINUTE" = "00" ] || [ "$MINUTE" = "15" ] || [ "$MINUTE" = "30" ] || [ "$MINUTE" = "45" ]; then
    log_msg "Iniciando backup binlog"
    sh "$SCRIPT_DIR/backup-binlog.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Backup binlog falhou"
fi

# Daily: todo dia às 2h
if [ "$MINUTE" = "00" ] && [ "$HOUR" = "02" ]; then
    log_msg "Iniciando backup daily"
    sh "$SCRIPT_DIR/backup-daily.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Backup daily falhou"
fi

# Weekly: Domingo às 3h
if [ "$MINUTE" = "00" ] && [ "$HOUR" = "03" ] && [ "$DAY" = "7" ]; then
    log_msg "Iniciando backup weekly"
    sh "$SCRIPT_DIR/backup-daily.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Backup weekly falhou"
    sh "$SCRIPT_DIR/cleanup-drive.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Cleanup falhou"
fi

# Cleanup Drive: Diário às 4h
if [ "$MINUTE" = "00" ] && [ "$HOUR" = "04" ]; then
    log_msg "Iniciando cleanup"
    sh "$SCRIPT_DIR/cleanup-drive.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Cleanup falhou"
fi

# Test Restore: Domingo às 4h
if [ "$MINUTE" = "00" ] && [ "$HOUR" = "04" ] && [ "$DAY" = "7" ]; then
    log_msg "Iniciando test restore"
    sh "$SCRIPT_DIR/test-restore.sh" >> "$LOG_FILE" 2>&1 || log_msg "ERRO: Test restore falhou"
fi

# Remover lock
rm -f "$LOCK_FILE"

log_msg "Verificação concluída"
