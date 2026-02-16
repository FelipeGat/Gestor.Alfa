<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => 'required|integer|exists:empresas,id',
            'tipo_pessoa' => 'required|string|in:fisica,juridica',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cpf' => 'nullable|cpf',
            'cnpj' => 'nullable|cnpj',
            'inscricao_estadual' => 'nullable|string|max:50',
            'inscricao_municipal' => 'nullable|string|max:50',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|size:2',
            'cep' => 'nullable|string|max:10',
            'ativo' => 'nullable|boolean',
            'observacoes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'empresa_id.required' => 'Empresa é obrigatória',
            'tipo_pessoa.required' => 'Tipo de pessoa é obrigatório',
            'tipo_pessoa.in' => 'Tipo de pessoa deve ser "fisica" ou "juridica"',
            'razao_social.required' => 'Razão social é obrigatória',
            'cpf.cpf' => 'CPF inválido',
            'cnpj.cnpj' => 'CNPJ inválido',
            'email.email' => 'Email inválido',
            'estado.size' => 'Estado deve ter 2 caracteres (UF)',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tipoPessoa = $this->tipo_pessoa;
            $cpf = $this->cpf;
            $cnpj = $this->cnpj;

            if ($tipoPessoa === 'fisica' && empty($cpf)) {
                $validator->errors()->add('cpf', 'CPF é obrigatório para pessoa física');
            }

            if ($tipoPessoa === 'juridica' && empty($cnpj)) {
                $validator->errors()->add('cnpj', 'CNPJ é obrigatório para pessoa jurídica');
            }
        });
    }
}
