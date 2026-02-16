<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContaPagarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'valor' => number_format($this->valor, 2, ',', '.'),
            'valor_pago' => $this->valor_pago ? number_format($this->valor_pago, 2, ',', '.') : null,
            'data_vencimento' => $this->data_vencimento?->format('d/m/Y'),
            'data_pagamento' => $this->data_pagamento?->format('d/m/Y'),
            'status' => $this->status,
            'forma_pagamento' => $this->forma_pagamento,
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'fornecedor' => $this->whenLoaded('fornecedor', fn () => $this->fornecedor ? [
                'id' => $this->fornecedor->id,
                'razao_social' => $this->fornecedor->razao_social,
                'nome_fantasia' => $this->fornecedor->nome_fantasia,
            ] : null),
            'conta' => $this->whenLoaded('conta', fn () => $this->conta ? [
                'id' => $this->conta->id,
                'nome' => $this->conta->nome,
                'categoria' => $this->conta->categoria ? [
                    'id' => $this->conta->categoria->id,
                    'nome' => $this->conta->categoria->nome,
                ] : null,
            ] : null),
            'centro_custo' => $this->whenLoaded('centroCusto', fn () => $this->centroCusto ? [
                'id' => $this->centroCusto->id,
                'nome' => $this->centroCusto->nome,
            ] : null),
            'conta_financeira' => $this->whenLoaded('conta', fn () => $this->conta ? [
                'id' => $this->conta->id,
                'nome' => $this->conta->nome,
            ] : null),
        ];
    }
}
