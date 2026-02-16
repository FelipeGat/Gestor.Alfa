<?php

namespace App\Actions;

use App\Actions\DTO\AtualizarClienteDTO;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class AtualizarClienteAction
{
    public function execute(int $clienteId, AtualizarClienteDTO $dto): Cliente
    {
        return DB::transaction(function () use ($clienteId, $dto) {
            $cliente = Cliente::find($clienteId);

            if (! $cliente) {
                throw new BusinessRuleException('Cliente não encontrado');
            }

            if ($dto->cpf) {
                $cpf = preg_replace('/[^0-9]/', '', $dto->cpf);
                $existente = Cliente::where('cpf', $cpf)
                    ->where('id', '!=', $clienteId)
                    ->first();
                if ($existente) {
                    throw new BusinessRuleException('Já existe outro cliente com este CPF');
                }
            }

            if ($dto->cnpj) {
                $cnpj = preg_replace('/[^0-9]/', '', $dto->cnpj);
                $existente = Cliente::where('cnpj', $cnpj)
                    ->where('id', '!=', $clienteId)
                    ->first();
                if ($existente) {
                    throw new BusinessRuleException('Já existe outro cliente com este CNPJ');
                }
            }

            $cliente->update(array_filter([
                'razao_social' => $dto->razaoSocial,
                'nome_fantasia' => $dto->nomeFantasia,
                'cpf' => $dto->cpf ? preg_replace('/[^0-9]/', '', $dto->cpf) : $cliente->cpf,
                'cnpj' => $dto->cnpj ? preg_replace('/[^0-9]/', '', $dto->cnpj) : $cliente->cnpj,
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
                'ativo' => $dto->ativo,
                'observacoes' => $dto->observacoes,
            ], fn ($value) => $value !== null));

            return $cliente;
        });
    }
}
