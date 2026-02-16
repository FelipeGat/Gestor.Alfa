<?php

namespace Tests\Unit\Actions;

use App\Actions\DTO\AtualizarClienteDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AtualizarClienteDTOTest extends TestCase
{
    public function test_criar_dto_com_dados_validos(): void
    {
        $dto = new AtualizarClienteDTO(
            razaoSocial: 'Novo Nome',
            email: 'novo@email.com'
        );

        $this->assertEquals('Novo Nome', $dto->razaoSocial);
        $this->assertEquals('novo@email.com', $dto->email);
    }

    public function test_criar_dto_com_email_invalido_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email inválido');

        new AtualizarClienteDTO(
            email: 'email-invalido'
        );
    }

    public function test_criar_dto_com_todos_os_campos(): void
    {
        $dto = new AtualizarClienteDTO(
            razaoSocial: 'Empresa Atualizada',
            nomeFantasia: 'Nome Fantasia',
            telefone: '(11) 99999-9999',
            email: 'atualizado@email.com',
            ativo: false,
            observacoes: 'Nova observação'
        );

        $this->assertEquals('Empresa Atualizada', $dto->razaoSocial);
        $this->assertEquals('Nome Fantasia', $dto->nomeFantasia);
        $this->assertFalse($dto->ativo);
    }

    public function test_from_array_cria_dto_corretamente(): void
    {
        $data = [
            'razao_social' => 'Razao Social',
            'nome_fantasia' => 'Nome Fantasia',
            'email' => 'teste@email.com',
            'ativo' => true,
        ];

        $dto = AtualizarClienteDTO::fromArray($data);

        $this->assertEquals('Razao Social', $dto->razaoSocial);
        $this->assertEquals('Nome Fantasia', $dto->nomeFantasia);
        $this->assertTrue($dto->ativo);
    }

    public function test_from_array_com_campos_nulos(): void
    {
        $data = [
            'razao_social' => null,
            'email' => null,
        ];

        $dto = AtualizarClienteDTO::fromArray($data);

        $this->assertNull($dto->razaoSocial);
        $this->assertNull($dto->email);
    }
}
