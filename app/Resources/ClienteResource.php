<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_pessoa' => $this->tipo_pessoa,
            'razao_social' => $this->razao_social,
            'nome_fantasia' => $this->nome_fantasia,
            'cpf' => $this->cpf ? $this->formatarCpf($this->cpf) : null,
            'cnpj' => $this->cnpj ? $this->formatarCnpj($this->cnpj) : null,
            'inscricao_estadual' => $this->inscricao_estadual,
            'inscricao_municipal' => $this->inscricao_municipal,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'endereco_completo' => $this->montarEndereco(),
            'ativo' => $this->ativo,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'empresa' => $this->whenLoaded('empresa', fn () => [
                'id' => $this->empresa->id,
                'nome_fantasia' => $this->empresa->nome_fantasia,
            ]),
            'contatos' => $this->whenLoaded('contatos', fn () => ClienteContatoResource::collection($this->contatos)),
        ];
    }

    private function formatarCpf(string $cpf): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    private function formatarCnpj(string $cnpj): string
    {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    private function montarEndereco(): ?string
    {
        $partes = array_filter([
            $this->endereco,
            $this->numero,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado,
            $this->cep,
        ]);

        return ! empty($partes) ? implode(', ', $partes) : null;
    }
}
