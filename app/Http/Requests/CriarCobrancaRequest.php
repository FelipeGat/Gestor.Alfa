<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarCobrancaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orcamento_id' => 'required|integer|exists:orcamentos,id',
            'valor' => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date|after_or_equal:today',
            'descricao' => 'nullable|string|max:500',
            'observacoes' => 'nullable|string',
            'gerar_boleto' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'orcamento_id.required' => 'Orçamento é obrigatório',
            'orcamento_id.exists' => 'Orçamento não encontrado',
            'valor.required' => 'Valor é obrigatório',
            'valor.min' => 'Valor deve ser maior que zero',
            'data_vencimento.required' => 'Data de vencimento é obrigatória',
            'data_vencimento.date' => 'Data de vencimento inválida',
            'data_vencimento.after_or_equal' => 'Data de vencimento deve ser hoje ou futura',
        ];
    }
}
