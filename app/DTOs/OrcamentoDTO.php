<?php

namespace App\DTOs;

class OrcamentoDTO
{
    public function __construct(
        public int $empresaId,
        public ?int $clienteId = null,
        public ?int $preClienteId = null,
        public string $descricao = '',
        public float $valorTotal = 0,
        public float $desconto = 0,
        public ?string $status = 'rascunho',
        public array $itens = []
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            empresaId: (int) $request->input('empresa_id'),
            clienteId: $request->input('cliente_id') ? (int) $request->input('cliente_id') : null,
            preClienteId: $request->input('pre_cliente_id') ? (int) $request->input('pre_cliente_id') : null,
            descricao: $request->input('descricao', ''),
            valorTotal: (float) $request->input('valor_total', 0),
            desconto: (float) $request->input('desconto', 0),
            status: $request->input('status', 'rascunho'),
            itens: $request->input('itens', [])
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'cliente_id' => $this->clienteId,
            'pre_cliente_id' => $this->preClienteId,
            'descricao' => $this->descricao,
            'valor_total' => $this->valorTotal,
            'desconto' => $this->desconto,
            'status' => $this->status,
            'itens' => $this->itens,
        ];
    }
}
