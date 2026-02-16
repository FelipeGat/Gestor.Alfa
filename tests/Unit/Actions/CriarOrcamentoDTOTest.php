<?php

namespace Tests\Unit\Actions;

use App\Actions\DTO\CriarOrcamentoDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CriarOrcamentoDTOTest extends TestCase
{
    public function test_criar_dto_com_dados_validos(): void
    {
        $dto = new CriarOrcamentoDTO(
            empresaId: 1,
            clienteId: 1,
            valorTotal: 1000.00,
            usuarioId: 1
        );

        $this->assertEquals(1, $dto->empresaId);
        $this->assertEquals(1, $dto->clienteId);
        $this->assertEquals(1000.00, $dto->valorTotal);
        $this->assertEquals(1, $dto->usuarioId);
    }

    public function test_criar_dto_com_empresa_invalida_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empresa ID é obrigatório');

        new CriarOrcamentoDTO(
            empresaId: 0,
            valorTotal: 1000.00
        );
    }

    public function test_criar_dto_com_valor_negativo_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor total não pode ser negativo');

        new CriarOrcamentoDTO(
            empresaId: 1,
            valorTotal: -100.00
        );
    }

    public function test_criar_dto_sem_cliente_e_pre_cliente_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cliente ou Pré-cliente é obrigatório');

        new CriarOrcamentoDTO(
            empresaId: 1,
            valorTotal: 1000.00
        );
    }

    public function test_criar_dto_com_pre_cliente_e_valido(): void
    {
        $dto = new CriarOrcamentoDTO(
            empresaId: 1,
            preClienteId: 1,
            valorTotal: 1000.00
        );

        $this->assertEquals(1, $dto->preClienteId);
        $this->assertNull($dto->clienteId);
    }

    public function test_from_array_cria_dto_corretamente(): void
    {
        $data = [
            'empresa_id' => 1,
            'cliente_id' => 2,
            'valor_total' => 1500.00,
            'desconto' => 100.00,
            'usuario_id' => 5,
        ];

        $dto = CriarOrcamentoDTO::fromArray($data);

        $this->assertEquals(1, $dto->empresaId);
        $this->assertEquals(2, $dto->clienteId);
        $this->assertEquals(1500.00, $dto->valorTotal);
        $this->assertEquals(100.00, $dto->desconto);
        $this->assertEquals(5, $dto->usuarioId);
    }

    public function test_from_array_com_itens(): void
    {
        $data = [
            'empresa_id' => 1,
            'cliente_id' => 1,
            'valor_total' => 1000.00,
            'itens' => [
                ['descricao' => 'Serviço 1', 'quantidade' => 1, 'valor_unitario' => 500.00],
                ['descricao' => 'Serviço 2', 'quantidade' => 2, 'valor_unitario' => 250.00],
            ],
        ];

        $dto = CriarOrcamentoDTO::fromArray($data);

        $this->assertCount(2, $dto->itens);
        $this->assertEquals('Serviço 1', $dto->itens[0]['descricao']);
    }
}
