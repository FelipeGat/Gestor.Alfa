<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrcamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_orcamento' => $this->numero_orcamento,
            'status' => $this->status,
            'descricao' => $this->descricao,
            'valor_total' => number_format($this->valor_total, 2, ',', '.'),
            'desconto' => number_format($this->desconto, 2, ',', '.'),
            'taxas' => number_format($this->taxas, 2, ',', '.'),
            'forma_pagamento' => $this->forma_pagamento,
            'prazo_pagamento' => $this->prazo_pagamento,
            'validade' => $this->validade?->format('d/m/Y'),
            'data_agendamento' => $this->data_agendamento?->format('d/m/Y'),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'empresa' => $this->whenLoaded('empresa', fn () => [
                'id' => $this->empresa->id,
                'nome_fantasia' => $this->empresa->nome_fantasia,
            ]),
            'cliente' => $this->whenLoaded('cliente', fn () => $this->cliente ? [
                'id' => $this->cliente->id,
                'nome_fantasia' => $this->cliente->nome_fantasia,
                'razao_social' => $this->cliente->razao_social,
            ] : null),
            'pre_cliente' => $this->whenLoaded('preCliente', fn () => $this->preCliente ? [
                'id' => $this->preCliente->id,
                'nome_fantasia' => $this->preCliente->nome_fantasia,
            ] : null),
            'itens' => $this->whenLoaded('itens', fn () => OrcamentoItemResource::collection($this->itens)),
            'centro_custo' => $this->whenLoaded('centroCusto', fn () => [
                'id' => $this->centroCusto->id,
                'nome' => $this->centroCusto->nome,
            ]),
        ];
    }
}
