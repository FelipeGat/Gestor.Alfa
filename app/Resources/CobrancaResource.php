<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CobrancaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orcamento_id' => $this->orcamento_id,
            'status' => $this->status,
            'valor' => number_format($this->valor, 2, ',', '.'),
            'valor_pago' => $this->valor_pago ? number_format($this->valor_pago, 2, ',', '.') : null,
            'data_vencimento' => $this->data_vencimento?->format('d/m/Y'),
            'data_pagamento' => $this->data_pagamento?->format('d/m/Y'),
            'descricao' => $this->descricao,
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'orcamento' => $this->whenLoaded('orcamento', fn () => [
                'id' => $this->orcamento->id,
                'numero_orcamento' => $this->orcamento->numero_orcamento,
                'cliente' => $this->orcamento->cliente ? [
                    'id' => $this->orcamento->cliente->id,
                    'nome_fantasia' => $this->orcamento->cliente->nome_fantasia,
                ] : null,
            ]),
        ];
    }
}
