<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class BaixarCobrancaDTO
{
    public function __construct(
        public int $cobrancaId,
        public float $valor,
        public string $dataPagamento,
        public string $formaPagamento,
        public int $contaFinanceiraId,
        public int $usuarioId,
        public ?string $observacoes = null
    ) {
        if ($cobrancaId <= 0) {
            throw new InvalidArgumentException('Cobrança é obrigatória');
        }

        if ($valor <= 0) {
            throw new InvalidArgumentException('Valor deve ser maior que zero');
        }

        if (empty($dataPagamento)) {
            throw new InvalidArgumentException('Data de pagamento é obrigatória');
        }

        if (empty($formaPagamento)) {
            throw new InvalidArgumentException('Forma de pagamento é obrigatória');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cobrancaId: (int) $data['cobranca_id'],
            valor: (float) $data['valor'],
            dataPagamento: $data['data_pagamento'] ?? now()->format('Y-m-d'),
            formaPagamento: $data['forma_pagamento'],
            contaFinanceiraId: (int) $data['conta_financeira_id'],
            usuarioId: (int) ($data['usuario_id'] ?? 0),
            observacoes: $data['observacoes'] ?? null
        );
    }
}
