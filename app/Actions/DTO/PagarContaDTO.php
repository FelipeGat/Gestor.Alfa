<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class PagarContaDTO
{
    public function __construct(
        public int $contaId,
        public float $valor,
        public string $dataPagamento,
        public int $contaFinanceiraId,
        public int $usuarioId
    ) {
        if ($contaId <= 0) {
            throw new InvalidArgumentException('Conta ID é obrigatório');
        }

        if ($valor <= 0) {
            throw new InvalidArgumentException('Valor deve ser maior que zero');
        }

        if ($contaFinanceiraId <= 0) {
            throw new InvalidArgumentException('Conta financeira é obrigatória');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            contaId: (int) $data['conta_id'],
            valor: (float) $data['valor'],
            dataPagamento: $data['data_pagamento'] ?? now()->format('Y-m-d'),
            contaFinanceiraId: (int) $data['conta_financeira_id'],
            usuarioId: (int) $data['usuario_id']
        );
    }
}
