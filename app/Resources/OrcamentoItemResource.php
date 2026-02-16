<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrcamentoItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'quantidade' => $this->quantidade,
            'valor_unitario' => number_format($this->valor_unitario, 2, ',', '.'),
            'valor_total' => number_format($this->valor_total, 2, ',', '.'),
            'tipo' => $this->tipo,
        ];
    }
}
