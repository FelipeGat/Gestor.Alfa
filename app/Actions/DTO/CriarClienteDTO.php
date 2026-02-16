<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class CriarClienteDTO
{
    public function __construct(
        public int $empresaId,
        public string $tipoPessoa,
        public string $razaoSocial,
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
        public bool $ativo = true,
        public ?string $observacoes = null
    ) {
        if ($empresaId <= 0) {
            throw new InvalidArgumentException('Empresa é obrigatória');
        }

        if (! in_array($tipoPessoa, ['fisica', 'juridica'])) {
            throw new InvalidArgumentException('Tipo de pessoa deve ser "fisica" ou "juridica"');
        }

        if (empty($razaoSocial)) {
            throw new InvalidArgumentException('Razão social é obrigatória');
        }

        if ($tipoPessoa === 'fisica' && empty($cpf)) {
            throw new InvalidArgumentException('CPF é obrigatório para pessoa física');
        }

        if ($tipoPessoa === 'juridica' && empty($cnpj)) {
            throw new InvalidArgumentException('CNPJ é obrigatório para pessoa jurídica');
        }

        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            empresaId: (int) $data['empresa_id'],
            tipoPessoa: $data['tipo_pessoa'],
            razaoSocial: $data['razao_social'],
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
            ativo: $data['ativo'] ?? true,
            observacoes: $data['observacoes'] ?? null
        );
    }
}
