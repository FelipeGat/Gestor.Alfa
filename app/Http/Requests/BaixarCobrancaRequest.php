<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaixarCobrancaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cobranca_id' => 'required|integer|exists:cobrancas,id',
            'valor' => 'required|numeric|min:0.01',
            'data_pagamento' => 'required|date',
            'forma_pagamento' => 'required|string|in:dinheiro,transferencia,boleto,cartao,pix,cheque,outro',
            'conta_financeira_id' => 'required|integer|exists:contas_financeiras,id',
            'observacoes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cobranca_id.required' => 'Cobrança é obrigatória',
            'cobranca_id.exists' => 'Cobrança não encontrada',
            'valor.required' => 'Valor é obrigatório',
            'valor.min' => 'Valor deve ser maior que zero',
            'data_pagamento.required' => 'Data de pagamento é obrigatória',
            'data_pagamento.date' => 'Data de pagamento inválida',
            'forma_pagamento.required' => 'Forma de pagamento é obrigatória',
            'forma_pagamento.in' => 'Forma de pagamento inválida',
            'conta_financeira_id.required' => 'Conta financeira é obrigatória',
            'conta_financeira_id.exists' => 'Conta financeira não encontrada',
        ];
    }
}
