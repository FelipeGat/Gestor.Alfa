<?php

namespace App\Actions;

use App\Actions\DTO\CriarClienteDTO;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class CriarClienteAction
{
    public function execute(CriarClienteDTO $dto): Cliente
    {
        return DB::transaction(function () use ($dto) {
            if ($dto->cpf) {
                $existente = Cliente::where('cpf', preg_replace('/[^0-9]/', '', $dto->cpf))->first();
                if ($existente) {
                    throw new BusinessRuleException('Já existe um cliente com este CPF');
                }
            }

            if ($dto->cnpj) {
                $existente = Cliente::where('cnpj', preg_replace('/[^0-9]/', '', $dto->cnpj))->first();
                if ($existente) {
                    throw new BusinessRuleException('Já existe um cliente com este CNPJ');
                }
            }

            $cliente = Cliente::create([
                'empresa_id' => $dto->empresaId,
                'tipo_pessoa' => $dto->tipoPessoa,
                'razao_social' => $dto->razaoSocial,
                'nome_fantasia' => $dto->nomeFantasia,
                'cpf' => $dto->cpf ? preg_replace('/[^0-9]/', '', $dto->cpf) : null,
                'cnpj' => $dto->cnpj ? preg_replace('/[^0-9]/', '', $dto->cnpj) : null,
                'inscricao_estadual' => $dto->inscricaoEstadual,
                'inscricao_municipal' => $dto->inscricaoMunicipal,
                'telefone' => $dto->telefone,
                'email' => $dto->email,
                'endereco' => $dto->endereco,
                'numero' => $dto->numero,
                'complemento' => $dto->complemento,
                'bairro' => $dto->bairro,
                'cidade' => $dto->cidade,
                'estado' => $dto->estado,
                'cep' => $dto->cep,
                'ativo' => $dto->ativo ?? true,
                'observacoes' => $dto->observacoes,
            ]);

            return $cliente;
        });
    }
}
