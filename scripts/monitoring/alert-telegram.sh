#!/bin/bash
# alert-telegram.sh - Envia notificações para o Telegram
#用法: ./alert-telegram.sh "mensagem" [severidade]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

if [ -f "$PROJECT_ROOT/.env" ]; then
    export $(grep -v '^#' "$PROJECT_ROOT/.env" | xargs)
fi

TELEGRAM_BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-}"
TELEGRAM_CHAT_ID="${TELEGRAM_CHAT_ID:-}"

LOG_FILE="$PROJECT_ROOT/backups/monitoring.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

send_telegram() {
    local message="$1"
    local severity="$2"
    
    if [ -z "$TELEGRAM_BOT_TOKEN" ] || [ -z "$TELEGRAM_CHAT_ID" ]; then
        log "AVISO: Telegram não configurado (TELEGRAM_BOT_TOKEN ou TELEGRAM_CHAT_ID vazio)"
        return 1
    fi
    
    local emoji=""
    case "$severity" in
        "CRITICAL") emoji="🚨" ;;
        "ERROR") emoji="❌" ;;
        "WARNING") emoji="⚠️" ;;
        "INFO") emoji="ℹ️" ;;
        *) emoji="📢" ;;
    esac
    
    local full_message="${emoji} *Gestor Alfa - $severity*

$message

⏰ $(date '+%d/%m/%Y às %H:%M:%S')"
    
    local url="https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage"
    
    local response=$(curl -s -X POST "$url" \
        -H "Content-Type: application/json" \
        -d "{
            \"chat_id\": \"$TELEGRAM_CHAT_ID\",
            \"text\": \"$full_message\",
            \"parse_mode\": \"Markdown\",
            \"disable_web_page_preview\": true
        }")
    
    if echo "$response" | grep -q '"ok":true'; then
        log "Notificação Telegram enviada com sucesso"
        return 0
    else
        log "ERRO ao enviar notificação Telegram: $response"
        return 1
    fi
}

if [ -n "$1" ]; then
    send_telegram "$1" "${2:-INFO}"
fi
