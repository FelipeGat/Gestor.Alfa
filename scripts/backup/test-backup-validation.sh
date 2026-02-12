#!/bin/bash
# test-backup-validation.sh - Valida backups criados

BACKUP_DIR="${BACKUP_DIR:-/backups}"
[ -d "$BACKUP_DIR" ] || BACKUP_DIR="/root/Gestor.Alfa/backups"

echo "=== Validacao de Backups ==="
echo "Diretorio: $BACKUP_DIR"
echo ""

echo "--- Backups Full ---"
if [ -d "$BACKUP_DIR/mysql" ]; then
    LATEST_FULL=$(ls -t "$BACKUP_DIR"/mysql/gestor_alfa_*.sql.gz 2>/dev/null | head -1)
    if [ -n "$LATEST_FULL" ]; then
        echo "Backup mais recente: $LATEST_FULL"
        SIZE=$(du -h "$LATEST_FULL" | cut -f1)
        echo "Tamanho: $SIZE"

        # Validar formato gzip
        if gunzip -t "$LATEST_FULL" 2>/dev/null; then
            echo "Formato gzip: OK"
        else
            echo "Formato gzip: INVALIDO"
        fi

        # Contar linhas
        LINES=$(gunzip -c "$LATEST_FULL" | wc -l)
        echo "Linhas: $LINES"

        if [ "$LINES" -gt 50 ]; then
            echo "Linhas: OK"
        else
            echo "Linhas: MUITO POUCAS"
        fi

        # Verificar conteudo
        MATCH_COUNT=$(gunzip -c "$LATEST_FULL" | grep -i -c "CREATE TABLE" 2>/dev/null || echo "0")
        echo "Tabelas encontradas: $MATCH_COUNT"

        if [ "$MATCH_COUNT" -gt 0 ]; then
            echo "Conteudo do banco: OK"
        else
            echo "Conteudo do banco: NAO ENCONTRADO"
        fi
    else
        echo "Nenhum backup encontrado"
    fi
else
    echo "Diretorio de backups nao encontrado: $BACKUP_DIR/mysql"
fi

echo ""

echo "--- Binary Logs ---"
if [ -d "$BACKUP_DIR/binlogs" ]; then
    BINLOG_COUNT=$(ls -1 "$BACKUP_DIR/binlogs"/mysql-bin.* 2>/dev/null | wc -l)
    echo "Binary logs encontrados: $BINLOG_COUNT"
    if [ "$BINLOG_COUNT" -gt 0 ]; then
        echo "Ultimos:"
        ls -la "$BACKUP_DIR/binlogs"/mysql-bin.* 2>/dev/null | tail -5
    fi
else
    echo "Diretorio de binlogs nao encontrado"
fi

echo ""
echo "=== Fim da Validacao ==="
