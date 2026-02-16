<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class BaixaLoteCobrancasDTO
{
    /** @var array<int, array{cobranca_id: int, valor: float, data_pagamento: string, forma_pagamento: string, conta_financeira_id: int}> */
    public array $cobrancas = [];

    public function __construct(
        public int $usuarioId,
        public array $cobrancasData = []
    ) {
        if (empty($cobrancasData)) {
            throw new InvalidArgumentException('Ao menos uma cobrança deve ser informada');
        }

        foreach ($cobrancasData as $index => $cobranca) {
            $this->validarCobranca($cobranca, $index);
            $this->cobrancas[] = [
                'cobranca_id' => (int) $cobranca['cobranca_id'],
                'valor' => (float) $cobranca['valor'],
                'data_pagamento' => $cobranca['data_pagamento'],
                'forma_pagamento' => $cobranca['forma_pagamento'],
                'conta_financeira_id' => (int) $cobranca['conta_financeira_id'],
            ];
        }
    }

    private function validarCobranca(array $cobranca, int $index): void
    {
        if (empty($cobranca['cobranca_id'])) {
            throw new InvalidArgumentException("Cobrança na posição {$index}: ID é obrigatório");
        }

        if (empty($cobranca['valor']) || $cobranca['valor'] <= 0) {
            throw new InvalidArgumentException("Cobrança na posição {$index}: Valor deve ser maior que zero");
        }

        if (empty($cobranca['data_pagamento'])) {
            throw new InvalidArgumentException("Cobrança na posição {$index}: Data de pagamento é obrigatória");
        }

        if (empty($cobranca['forma_pagamento'])) {
            throw new InvalidArgumentException("Cobrança na posição {$index}: Forma de pagamento é obrigatória");
        }

        if (empty($cobranca['conta_financeira_id'])) {
            throw new InvalidArgumentException("Cobrança na posição {$index}: Conta financeira é obrigatória");
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            usuarioId: (int) ($data['usuario_id'] ?? 0),
            cobrancasData: $data['cobrancas'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'usuario_id' => $this->usuarioId,
            'cobrancas' => $this->cobrancas,
        ];
    }
}
