#!/bin/bash
# health-check.sh - Verifica saúde do MySQL e backups
# Executar via cron: */10 * * * * /root/Gestor.Alfa/scripts/monitoring/health-check.sh

export TZ="America/Sao_Paulo"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

if [ -f "$PROJECT_ROOT/.env" ]; then
    export $(grep -v '^#' "$PROJECT_ROOT/.env" | xargs)
fi

DB_HOST="${DB_HOST:-mysql}"
DB_NAME="${DB_DATABASE:-gestor_alfa}"
DB_USER="${DB_USERNAME:-gestor_user}"
DB_PASSWORD="${DB_PASSWORD:-}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-}"

BACKUP_DIR="$PROJECT_ROOT/backups/mysql"
MAX_BACKUP_AGE_HOURS="${MAX_BACKUP_AGE_HOURS:-4}"

LOG_FILE="$PROJECT_ROOT/backups/monitoring.log"
ALERT_SCRIPT="$SCRIPT_DIR/alert-telegram.sh"
LAST_STATUS_FILE="$PROJECT_ROOT/backups/last_health_status"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

send_alert() {
    local message="$1"
    local severity="$2"
    log "$severity: $message"
    "$ALERT_SCRIPT" "$message" "$severity"
}

check_mysql_online() {
    log "Verificando MySQL online..."
    
    if docker exec gestor_mysql mysqladmin ping -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" --silent 2>/dev/null; then
        log "MySQL está online"
        
        local last_status=$(cat "$LAST_STATUS_FILE" 2>/dev/null)
        if [ "$last_status" = "MYSQL_OFFLINE" ]; then
            send_alert "✅ MySQL está novamente ONLINE! (Problema resolvido)" "INFO"
        fi
        return 0
    else
        send_alert "🚨 MySQL está OFFLINE ou inacessível!" "CRITICAL"
        echo "MYSQL_OFFLINE" > "$LAST_STATUS_FILE"
        return 1
    fi
}

check_database_exists() {
    log "Verificando se banco '$DB_NAME' existe..."
    
    local result=$(docker exec gestor_mysql mysql -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT 1 FROM $DB_NAME.users LIMIT 1" 2>/dev/null)
    
    if [ $? -eq 0 ] || [ -n "$result" ]; then
        log "Banco '$DB_NAME' existe e tabelas estão acessíveis"
        
        local last_status=$(cat "$LAST_STATUS_FILE" 2>/dev/null)
        if [ "$last_status" = "DATABASE_MISSING" ]; then
            send_alert "✅ Banco '$DB_NAME' está novamente acessível! (Problema resolvido)" "INFO"
            echo "OK" > "$LAST_STATUS_FILE"
        fi
        return 0
    else
        local error_msg="Banco '$DB_NAME' NÃO encontrado ou tabelas inacessíveis!
        
Possíveis causas:
• Banco deletado acidentalmente
• Tabelas corrompidas
• Erro de migração

🔧 Ação necessária:
Execute o script de restauração:
cd $PROJECT_ROOT
./scripts/monitoring/restore-interactive.sh"
        
        send_alert "$error_msg" "CRITICAL"
        echo "DATABASE_MISSING" > "$LAST_STATUS_FILE"
        return 1
    fi
}

check_backup_recent() {
    log "Verificando backups..."
    
    if [ ! -d "$BACKUP_DIR" ]; then
        send_alert "Diretório de backup não encontrado: $BACKUP_DIR" "ERROR"
        return 1
    fi
    
    local latest_backup=$(ls -t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | head -1)
    
    if [ -z "$latest_backup" ]; then
        send_alert "NENHUM backup encontrado em $BACKUP_DIR!" "CRITICAL"
        echo "NO_BACKUP" > "$LAST_STATUS_FILE"
        return 1
    fi
    
    local backup_age_seconds=$(($(date +%s) - $(stat -c %Y "$latest_backup")))
    local backup_age_hours=$((backup_age_seconds / 3600))
    
    if [ "$backup_age_hours" -gt "$MAX_BACKUP_AGE_HOURS" ]; then
        local backup_date=$(date -r "$latest_backup" '+%d/%m/%Y às %H:%M')
        send_alert "⚠️ Backup muito antigo!

Último backup: $backup_date
Idade: ${backup_age_hours}h (máximo: ${MAX_BACKUP_AGE_HOURS}h)

🔧 Ação necessária:
Execute backup manual:
cd $PROJECT_ROOT
./scripts/backup/backup-full.sh" "WARNING"
        echo "OLD_BACKUP" > "$LAST_STATUS_FILE"
        return 1
    fi
    
    log "Backup recente encontrado: $latest_backup (${backup_age_hours}h)"
    
    local last_status=$(cat "$LAST_STATUS_FILE" 2>/dev/null)
    if [ "$last_status" = "NO_BACKUP" ] || [ "$last_status" = "OLD_BACKUP" ]; then
        send_alert "✅ Backup está OK novamente! (${backup_age_hours}h)" "INFO"
    fi
    
    return 0
}

check_backup_integrity() {
    log "Verificando integridade do último backup..."
    
    local latest_backup=$(ls -t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | head -1)
    
    if ! gunzip -t "$latest_backup" 2>/dev/null; then
        send_alert "❌ Backup corrompido: $(basename $latest_backup)" "CRITICAL"
        echo "CORRUPT_BACKUP" > "$LAST_STATUS_FILE"
        return 1
    fi
    
    log "Backup válido"
    return 0
}

check_disk_space() {
    log "Verificando espaço em disco..."
    
    local disk_usage=$(df -h "$PROJECT_ROOT" | tail -1 | awk '{print $5}' | sed 's/%//')
    
    if [ "$disk_usage" -gt 80 ]; then
        send_alert "⚠️ Disco quase cheio: ${disk_usage}% utilizado" "WARNING"
        return 1
    fi
    
    log "Espaço em disco OK: ${disk_usage}%"
    return 0
}

main() {
    log "=== Iniciando health-check ==="
    
    local status="OK"
    
    if ! check_mysql_online; then
        status="MYSQL_OFFLINE"
    elif ! check_database_exists; then
        status="DATABASE_MISSING"
    elif ! check_backup_recent; then
        status="OLD_BACKUP"
    elif ! check_backup_integrity; then
        status="CORRUPT_BACKUP"
    else
        status="OK"
        check_disk_space
    fi
    
    echo "$status" > "$LAST_STATUS_FILE"
    log "=== Health-check finalizado: $status ==="
    log ""
}

main "$@"
