<?php

namespace App\DTOs;

class CobrancaDTO
{
    public function __construct(
        public int $orcamentoId,
        public float $valor,
        public string $dataVencimento,
        public ?string $descricao = null,
        public ?string $observacoes = null,
        public int $usuarioId = 0
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            orcamentoId: (int) $request->input('orcamento_id'),
            valor: (float) $request->input('valor'),
            dataVencimento: $request->input('data_vencimento'),
            descricao: $request->input('descricao'),
            observacoes: $request->input('observacoes'),
            usuarioId: $request->user()?->id ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'orcamento_id' => $this->orcamentoId,
            'valor' => $this->valor,
            'data_vencimento' => $this->dataVencimento,
            'descricao' => $this->descricao,
            'observacoes' => $this->observacoes,
            'usuario_id' => $this->usuarioId,
        ];
    }
}
