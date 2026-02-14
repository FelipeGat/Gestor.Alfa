#!/bin/bash
# setup-cron.sh - Configura o cron para executar health-check

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

CRON_ENTRY="*/10 * * * * /root/Gestor.Alfa/scripts/monitoring/health-check.sh >> /root/Gestor.Alfa/backups/monitoring.log 2>&1"

install_cron() {
    echo "Instalando cron job para health-check (a cada 10 minutos)..."
    
    (crontab -l 2>/dev/null | grep -v "health-check.sh"; echo "$CRON_ENTRY") | crontab -
    
    if [ $? -eq 0 ]; then
        echo "✅ Cron job instalado com sucesso!"
        echo ""
        echo "Entrada adicionada:"
        echo "$CRON_ENTRY"
        echo ""
        echo "Para verificar: crontab -l"
        echo "Para remover: ./scripts/monitoring/remove-cron.sh"
    else
        echo "❌ Erro ao instalar cron job"
        exit 1
    fi
}

remove_cron() {
    echo "Removendo cron job de health-check..."
    crontab -l 2>/dev/null | grep -v "health-check.sh" | crontab -
    echo "✅ Cron job removido"
}

show_cron() {
    echo "Cron jobs atuais:"
    crontab -l 2>/dev/null || echo "(nenhum cron job configurado)"
}

case "$1" in
    install)
        install_cron
        ;;
    remove)
        remove_cron
        ;;
    show)
        show_cron
        ;;
    *)
        echo "用法: $0 {install|remove|show}"
        echo ""
        echo "  install - Instala o cron job (a cada 10 minutos)"
        echo "  remove  - Remove o cron job"
        echo "  show    - Mostra cron jobs atuais"
        exit 1
        ;;
esac
