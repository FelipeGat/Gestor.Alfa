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
            self::RASCUNHO->value => [self::ENVIADO->value, self::CANCELADO->value],
            self::ENVIADO->value => [self::APROVADO->value, self::REJEITADO->value, self::RASCUNHO->value],
            self::APROVADO->value => [self::FINANCEIRO->value, self::CANCELADO->value],
            self::FINANCEIRO->value => [self::EXECUTANDO->value, self::CANCELADO->value],
            self::EXECUTANDO->value => [self::CONCLUIDO->value, self::CANCELADO->value],
            self::REJEITADO->value => [self::RASCUNHO->value],
            self::CANCELADO->value => [],
            self::CONCLUIDO->value => [],
        ];

        return in_array($novoStatus->value, $transicoes[$this->value] ?? [], true);
    }
}
