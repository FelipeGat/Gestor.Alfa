#!/bin/bash
# restore-interactive.sh - Restauração interativa do banco de dados
# Uso: ./scripts/monitoring/restore-interactive.sh

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
LOG_FILE="$PROJECT_ROOT/backups/monitoring.log"
ALERT_SCRIPT="$SCRIPT_DIR/alert-telegram.sh"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"
}

header() {
    echo -e "\n${BLUE}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}\n"
}

confirm() {
    local prompt="$1"
    local response
    echo -en "$prompt [s/N]: "
    read response
    case "$response" in
        s|S|sim|SIM) return 0 ;;
        *) return 1 ;;
    esac
}

check_mysql_running() {
    if docker ps --format '{{.Names}}' | grep -q "^gestor_mysql$"; then
        return 0
    else
        error "Container MySQL não está rodando!"
        return 1
    fi
}

list_backups() {
    header "Backups Disponíveis"
    
    if [ ! -d "$BACKUP_DIR" ]; then
        error "Diretório de backup não encontrado: $BACKUP_DIR"
        return 1
    fi
    
    local backups=($(ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null))
    
    if [ ${#backups[@]} -eq 0 ]; then
        error "Nenhum backup encontrado!"
        return 1
    fi
    
    echo -e "${YELLOW}Selecione o backup para restaurar:${NC}\n"
    
    local count=1
    for backup in "${backups[@]}"; do
        local size=$(du -h "$backup" | cut -f1)
        local date=$(date -r "$backup" '+%d/%m/%Y às %H:%M')
        local age_hours=$(echo "($(date +%s) - $(stat -c %Y "$backup")) / 3600" | bc)
        
        local age_str=""
        if [ "$age_hours" -lt 24 ]; then
            age_str="(${age_hours}h)"
        else
            local days=$(echo "$age_hours / 24" | bc)
            age_str="(${days}d)"
        fi
        
        echo -e "  ${GREEN}$count)${NC} $(basename "$backup")"
        echo -e "      📦 $size  📅 $date $age_str"
        echo
        
        count=$((count + 1))
    done
    
    echo -e "${YELLOW}0)${NC} Cancelar\n"
    
    return 0
}

select_backup() {
    local backups=($(ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null))
    
    while true; do
        echo -en "Digite o número do backup: "
        read choice
        
        if [ "$choice" = "0" ] || [ -z "$choice" ]; then
            echo "Operação cancelada."
            exit 0
        fi
        
        if [[ "$choice" =~ ^[0-9]+$ ]] && [ "$choice" -ge 1 ] && [ "$choice" -le ${#backups[@]} ]; then
            SELECTED_BACKUP="${backups[$((choice - 1))]}"
            return 0
        else
        
            echo -e "${RED}Opção inválida!${NC}"
        fi
    done
}

stop_app() {
    header "Parando Aplicação"
    
    log "Parando containers da aplicação..."
    docker compose stop php-fpm queue-worker 2>/dev/null
    
    if [ $? -eq 0 ]; then
        log "Aplicação parada com sucesso"
    else
        warn "Falha ao parar aplicação (pode já estar parada)"
    fi
}

start_app() {
    header "Iniciando Aplicação"
    
    log "Iniciando containers da aplicação..."
    docker compose start php-fpm queue-worker 2>/dev/null
    
    if [ $? -eq 0 ]; then
        log "Aplicação iniciada com sucesso"
    else
        error "Falha ao iniciar aplicação"
    fi
}

drop_and_create_database() {
    header "Recriando Banco de Dados"
    
    log "Recriando banco '$DB_NAME'..."
    
    docker exec gestor_mysql mysql -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" -e "
        DROP DATABASE IF EXISTS $DB_NAME;
        CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    " 2>/dev/null
    
    if [ $? -eq 0 ]; then
        log "Banco '$DB_NAME' recriado com sucesso"
    else
        error "Falha ao recriar banco"
        return 1
    fi
    
    return 0
}

restore_backup() {
    header "Restauração do Backup"
    
    log "Restaurando backup: $(basename $SELECTED_BACKUP)"
    
    gunzip < "$SELECTED_BACKUP" | docker exec -i gestor_mysql mysql -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME"
    
    if [ $? -eq 0 ]; then
        log "Restauração concluída com sucesso!"
        return 0
    else
        error "Falha durante restauração"
        return 1
    fi
}

verify_restore() {
    header "Verificação Pós-Restauração"
    
    log "Verificando integridade..."
    
    local table_count=$(docker exec gestor_mysql mysql -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME'" 2>/dev/null)
    
    if [ -n "$table_count" ] && [ "$table_count" -gt 0 ]; then
        log "✅ Banco restaurado: $table_count tabelas encontradas"
    else
        warn "Não foi possível verificar tabelas"
    fi
    
    local user_count=$(docker exec gestor_mysql mysql -h localhost -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "SELECT COUNT(*) FROM $DB_NAME.users" 2>/dev/null)
    
    if [ -n "$user_count" ]; then
        log "✅ Tabela users: $user_count registros"
    fi
    
    return 0
}

notify_restore() {
    local status="$1"
    local backup_name=$(basename "$SELECTED_BACKUP")
    
    if [ -x "$ALERT_SCRIPT" ]; then
        local message="🔄 Restauração de banco realizada
        
Backup: $backup_name
Status: $status
Data: $(date '+%d/%m/%Y às %H:%M:%S')
Usuário: $(whoami)"
        
        if [ "$status" = "SUCESSO" ]; then
            "$ALERT_SCRIPT" "$message" "INFO"
        else
            "$ALERT_SCRIPT" "$message" "ERROR"
        fi
    fi
}

main() {
    header "🎯 Restauração Interativa do Banco de Dados"
    
    echo -e "${YELLOW}AVISO: Este processo irá substituir todos os dados atuais!${NC}\n"
    
    if ! confirm "Continuar com a restauração?"; then
        echo "Operação cancelada."
        exit 0
    fi
    
    if ! check_mysql_running; then
        error "Verifique se o MySQL está rodando e tente novamente"
        exit 1
    fi
    
    list_backups || exit 1
    select_backup || exit 1
    
    header "Backup Selecionado"
    echo -e "📦 $(basename $SELECTED_BACKUP)\n"
    
    if ! confirm "Confirmar restauração deste backup?"; then
        echo "Operação cancelada."
        exit 0
    fi
    
    stop_app || exit 1
    
    if ! drop_and_create_database; then
        error "Falha ao recriar banco"
        start_app
        exit 1
    fi
    
    if ! restore_backup; then
        error "Falha na restauração"
        notify_restore "FALHA"
        start_app
        exit 1
    fi
    
    verify_restore
    
    start_app
    
    header "✅ Restauração Concluída"
    
    log "Banco '$DB_NAME' restaurado com sucesso!"
    log "Backup utilizado: $(basename $SELECTED_BACKUP)"
    
    notify_restore "SUCESSO"
    
    echo -e "\n${GREEN}Processo finalizado!${NC}\n"
}

main "$@"
