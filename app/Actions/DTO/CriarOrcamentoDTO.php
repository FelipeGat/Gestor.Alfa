<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class CriarOrcamentoDTO
{
    public function __construct(
        public int $empresaId,
        public ?int $atendimentoId = null,
        public ?int $clienteId = null,
        public ?int $preClienteId = null,
        public string $descricao = '',
        public float $valorTotal = 0,
        public float $desconto = 0,
        public float $taxas = 0,
        public array $descricaoTaxas = [],
        public ?string $formaPagamento = null,
        public ?string $prazoPagamento = null,
        public ?string $validade = null,
        public ?string $observacoes = null,
        public string $status = 'rascunho',
        public int $usuarioId = 0,
        public array $itens = []
    ) {
        if ($empresaId <= 0) {
            throw new InvalidArgumentException('Empresa ID é obrigatório');
        }

        if ($valorTotal < 0) {
            throw new InvalidArgumentException('Valor total não pode ser negativo');
        }

        if ($desconto < 0) {
            throw new InvalidArgumentException('Desconto não pode ser negativo');
        }

        if (! $clienteId && ! $preClienteId) {
            throw new InvalidArgumentException('Cliente ou Pré-cliente é obrigatório');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            empresaId: $data['empresa_id'] ?? 0,
            atendimentoId: $data['atendimento_id'] ?? null,
            clienteId: $data['cliente_id'] ?? null,
            preClienteId: $data['pre_cliente_id'] ?? null,
            descricao: $data['descricao'] ?? '',
            valorTotal: (float) ($data['valor_total'] ?? 0),
            desconto: (float) ($data['desconto'] ?? 0),
            taxas: (float) ($data['taxas'] ?? 0),
            descricaoTaxas: $data['descricao_taxas'] ?? [],
            formaPagamento: $data['forma_pagamento'] ?? null,
            prazoPagamento: $data['prazo_pagamento'] ?? null,
            validade: $data['validade'] ?? null,
            observacoes: $data['observacoes'] ?? null,
            status: $data['status'] ?? 'rascunho',
            usuarioId: $data['usuario_id'] ?? 0,
            itens: $data['itens'] ?? []
        );
    }
}
