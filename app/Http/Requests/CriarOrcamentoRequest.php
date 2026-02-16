<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarOrcamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => 'required|integer|exists:empresas,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'pre_cliente_id' => 'nullable|integer|exists:pre_clientes,id',
            'atendimento_id' => 'nullable|integer|exists:atendimentos,id',
            'descricao' => 'nullable|string|max:500',
            'valor_total' => 'required|numeric|min:0',
            'desconto' => 'nullable|numeric|min:0',
            'taxas' => 'nullable|numeric|min:0',
            'descricao_taxas' => 'nullable|array',
            'forma_pagamento' => 'nullable|string|max:100',
            'prazo_pagamento' => 'nullable|string|max:50',
            'validade' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'status' => 'nullable|string|in:rascunho,enviado,aprovado,rejeitado,financeiro,cancelado',
            'itens' => 'nullable|array',
            'itens.*.descricao' => 'required|string|max:255',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
            'itens.*.tipo' => 'nullable|string|in:servico,produto',
        ];
    }

    public function messages(): array
    {
        return [
            'empresa_id.required' => 'Empresa é obrigatória',
            'empresa_id.exists' => 'Empresa não encontrada',
            'valor_total.required' => 'Valor total é obrigatório',
            'valor_total.min' => 'Valor total deve ser maior que zero',
            'cliente_id.required_without' => 'Cliente ou pré-cliente é obrigatório',
            'pre_cliente_id.required_without' => 'Cliente ou pré-cliente é obrigatório',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->cliente_id && ! $this->pre_cliente_id) {
                $validator->errors()->add('cliente_id', 'Cliente ou pré-cliente é obrigatório');
                $validator->errors()->add('pre_cliente_id', 'Cliente ou pré-cliente é obrigatório');
            }
        });
    }
}
