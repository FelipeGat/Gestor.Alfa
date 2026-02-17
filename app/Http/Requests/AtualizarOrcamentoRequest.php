<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtualizarOrcamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => 'nullable|integer|exists:empresas,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'pre_cliente_id' => 'nullable|integer|exists:pre_clientes,id',
            'descricao' => 'nullable|string|max:500',
            'valor_total' => 'nullable|numeric|min:0',
            'desconto' => 'nullable|numeric|min:0',
            'taxas' => 'nullable|numeric|min:0',
            'descricao_taxas' => 'nullable|array',
            'forma_pagamento' => 'nullable|string|max:100',
            'prazo_pagamento' => 'nullable|string|max:50',
            'validade' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'centro_custo_id' => 'nullable|integer|exists:centro_custos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'empresa_id.exists' => 'Empresa não encontrada',
            'cliente_id.exists' => 'Cliente não encontrado',
            'pre_cliente_id.exists' => 'Pré-cliente não encontrado',
            'valor_total.min' => 'Valor total não pode ser negativo',
            'desconto.min' => 'Desconto não pode ser negativo',
            'taxas.min' => 'Taxas não podem ser negativas',
            'centro_custo_id.exists' => 'Centro de custo não encontrado',
        ];
    }
}
