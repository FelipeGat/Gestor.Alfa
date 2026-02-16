<?php

namespace Tests\Unit\Actions;

use App\Actions\DTO\CriarClienteDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CriarClienteDTOTest extends TestCase
{
    public function test_criar_dto_pessoa_fisica_com_dados_validos(): void
    {
        $dto = new CriarClienteDTO(
            empresaId: 1,
            tipoPessoa: 'fisica',
            razaoSocial: 'João Silva',
            nomeFantasia: 'João Silva ME',
            cpf: '123.456.789-00',
            email: 'joao@email.com'
        );

        $this->assertEquals(1, $dto->empresaId);
        $this->assertEquals('fisica', $dto->tipoPessoa);
        $this->assertEquals('João Silva', $dto->razaoSocial);
    }

    public function test_criar_dto_pessoa_juridica_com_dados_validos(): void
    {
        $dto = new CriarClienteDTO(
            empresaId: 1,
            tipoPessoa: 'juridica',
            razaoSocial: 'Empresa Ltda',
            nomeFantasia: 'Empresa',
            cnpj: '12.345.678/0001-90',
            email: 'contato@empresa.com'
        );

        $this->assertEquals('juridica', $dto->tipoPessoa);
        $this->assertEquals('12.345.678/0001-90', $dto->cnpj);
    }

    public function test_criar_dto_sem_empresa_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empresa é obrigatória');

        new CriarClienteDTO(
            empresaId: 0,
            tipoPessoa: 'fisica',
            razaoSocial: 'João Silva',
            cpf: '123.456.789-00'
        );
    }

    public function test_criar_dto_tipo_pessoa_invalido_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de pessoa deve ser "fisica" ou "juridica"');

        new CriarClienteDTO(
            empresaId: 1,
            tipoPessoa: 'invalid',
            razaoSocial: 'João Silva'
        );
    }

    public function test_criar_dto_pessoa_fisica_sem_cpf_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF é obrigatório para pessoa física');

        new CriarClienteDTO(
            empresaId: 1,
            tipoPessoa: 'fisica',
            razaoSocial: 'João Silva'
        );
    }

    public function test_criar_dto_pessoa_juridica_sem_cnpj_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ é obrigatório para pessoa jurídica');

        new CriarClienteDTO(
            empresaId: 1,
            tipoPessoa: 'juridica',
            razaoSocial: 'Empresa Ltda'
        );
    }

    public function test_criar_dto_com_email_invalido_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email inválido');

        new CriarClienteDTO(
            empresaId: 1,
            tipoPessoa: 'fisica',
            razaoSocial: 'João Silva',
            cpf: '123.456.789-00',
            email: 'email-invalido'
        );
    }

    public function test_from_array_cria_dto_corretamente(): void
    {
        $data = [
            'empresa_id' => 1,
            'tipo_pessoa' => 'fisica',
            'razao_social' => 'Maria Santos',
            'nome_fantasia' => 'Maria',
            'cpf' => '987.654.321-00',
            'email' => 'maria@email.com',
            'telefone' => '(11) 99999-9999',
        ];

        $dto = CriarClienteDTO::fromArray($data);

        $this->assertEquals(1, $dto->empresaId);
        $this->assertEquals('Maria Santos', $dto->razaoSocial);
        $this->assertEquals('(11) 99999-9999', $dto->telefone);
    }
}
