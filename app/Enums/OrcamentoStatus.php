<?php

namespace App\Enums;

enum OrcamentoStatus: string
{
    case RASCUNHO = 'rascunho';
    case ENVIADO = 'enviado';
    case APROVADO = 'aprovado';
    case REJEITADO = 'rejeitado';
    case FINANCEIRO = 'financeiro';
    case EXECUTANDO = 'executando';
    case CONCLUIDO = 'concluido';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::ENVIADO => 'Enviado',
            self::APROVADO => 'Aprovado',
            self::REJEITADO => 'Rejeitado',
            self::FINANCEIRO => 'Financeiro',
            self::EXECUTANDO => 'Executando',
            self::CONCLUIDO => 'Concluído',
            self::CANCELADO => 'Cancelado',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function podeTransicionarPara(OrcamentoStatus $novoStatus): bool
    {
        $transicoes = [
            self::RASCUNHO => [self::ENVIADO, self::CANCELADO],
            self::ENVIADO => [self::APROVADO, self::REJEITADO, self::RASCUNHO],
            self::APROVADO => [self::FINANCEIRO, self::CANCELADO],
            self::FINANCEIRO => [self::EXECUTANDO, self::CANCELADO],
            self::EXECUTANDO => [self::CONCLUIDO, self::CANCELADO],
            self::REJEITADO => [self::RASCUNHO],
            self::CANCELADO => [],
            self::CONCLUIDO => [],
        ];

        return in_array($novoStatus, $transicoes[$this] ?? []);
    }
}
