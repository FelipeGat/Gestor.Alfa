<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class AtualizarClienteDTO
{
    public function __construct(
        public ?string $razaoSocial = null,
        public ?string $nomeFantasia = null,
        public ?string $cpf = null,
        public ?string $cnpj = null,
        public ?string $inscricaoEstadual = null,
        public ?string $inscricaoMunicipal = null,
        public ?string $telefone = null,
        public ?string $email = null,
        public ?string $endereco = null,
        public ?string $numero = null,
        public ?string $complemento = null,
        public ?string $bairro = null,
        public ?string $cidade = null,
        public ?string $estado = null,
        public ?string $cep = null,
        public ?bool $ativo = null,
        public ?string $observacoes = null
    ) {
        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            razaoSocial: $data['razao_social'] ?? null,
            nomeFantasia: $data['nome_fantasia'] ?? null,
            cpf: $data['cpf'] ?? null,
            cnpj: $data['cnpj'] ?? null,
            inscricaoEstadual: $data['inscricao_estadual'] ?? null,
            inscricaoMunicipal: $data['inscricao_municipal'] ?? null,
            telefone: $data['telefone'] ?? null,
            email: $data['email'] ?? null,
            endereco: $data['endereco'] ?? null,
            numero: $data['numero'] ?? null,
            complemento: $data['complemento'] ?? null,
            bairro: $data['bairro'] ?? null,
            cidade: $data['cidade'] ?? null,
            estado: $data['estado'] ?? null,
            cep: $data['cep'] ?? null,
            ativo: $data['ativo'] ?? null,
            observacoes: $data['observacoes'] ?? null
        );
    }
}
