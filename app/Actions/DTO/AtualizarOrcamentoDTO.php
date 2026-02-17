<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class AtualizarOrcamentoDTO
{
    public function __construct(
        public ?int $empresaId = null,
        public ?int $clienteId = null,
        public ?int $preClienteId = null,
        public ?string $descricao = null,
        public ?float $valorTotal = null,
        public ?float $desconto = null,
        public ?float $taxas = null,
        public ?array $descricaoTaxas = null,
        public ?string $formaPagamento = null,
        public ?string $prazoPagamento = null,
        public ?string $validade = null,
        public ?string $observacoes = null,
        public ?int $centroCustoId = null
    ) {
        if ($this->valorTotal !== null && $this->valorTotal < 0) {
            throw new InvalidArgumentException('Valor total não pode ser negativo');
        }

        if ($this->desconto !== null && $this->desconto < 0) {
            throw new InvalidArgumentException('Desconto não pode ser negativo');
        }

        if ($this->taxas !== null && $this->taxas < 0) {
            throw new InvalidArgumentException('Taxas não podem ser negativas');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            empresaId: $data['empresa_id'] ?? null,
            clienteId: $data['cliente_id'] ?? null,
            preClienteId: $data['pre_cliente_id'] ?? null,
            descricao: $data['descricao'] ?? null,
            valorTotal: isset($data['valor_total']) ? (float) $data['valor_total'] : null,
            desconto: isset($data['desconto']) ? (float) $data['desconto'] : null,
            taxas: isset($data['taxas']) ? (float) $data['taxas'] : null,
            descricaoTaxas: $data['descricao_taxas'] ?? null,
            formaPagamento: $data['forma_pagamento'] ?? null,
            prazoPagamento: $data['prazo_pagamento'] ?? null,
            validade: $data['validade'] ?? null,
            observacoes: $data['observacoes'] ?? null,
            centroCustoId: $data['centro_custo_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'empresa_id' => $this->empresaId,
            'cliente_id' => $this->clienteId,
            'pre_cliente_id' => $this->preClienteId,
            'descricao' => $this->descricao,
            'valor_total' => $this->valorTotal,
            'desconto' => $this->desconto,
            'taxas' => $this->taxas,
            'descricao_taxas' => $this->descricaoTaxas,
            'forma_pagamento' => $this->formaPagamento,
            'prazo_pagamento' => $this->prazoPagamento,
            'validade' => $this->validade,
            'observacoes' => $this->observacoes,
            'centro_custo_id' => $this->centroCustoId,
        ], fn ($value) => $value !== null);
    }
}
