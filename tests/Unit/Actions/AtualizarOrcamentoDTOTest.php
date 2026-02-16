<?php

namespace Tests\Unit\Actions;

use App\Actions\DTO\AtualizarOrcamentoDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AtualizarOrcamentoDTOTest extends TestCase
{
    public function test_criar_dto_com_dados_validos(): void
    {
        $dto = new AtualizarOrcamentoDTO(
            descricao: 'Nova descrição',
            valorTotal: 1500.00,
            desconto: 100.00
        );

        $this->assertEquals('Nova descrição', $dto->descricao);
        $this->assertEquals(1500.00, $dto->valorTotal);
        $this->assertEquals(100.00, $dto->desconto);
    }

    public function test_criar_dto_com_valor_negativo_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor total não pode ser negativo');

        new AtualizarOrcamentoDTO(valorTotal: -100.00);
    }

    public function test_criar_dto_com_desconto_negativo_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Desconto não pode ser negativo');

        new AtualizarOrcamentoDTO(desconto: -50.00);
    }

    public function test_criar_dto_com_taxas_negativas_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Taxas não podem ser negativas');

        new AtualizarOrcamentoDTO(taxas: -10.00);
    }

    public function test_from_array_cria_dto_corretamente(): void
    {
        $data = [
            'descricao' => 'Teste',
            'valor_total' => 2000.00,
            'desconto' => 200.00,
            'taxas' => 50.00,
            'forma_pagamento' => 'boleto',
        ];

        $dto = AtualizarOrcamentoDTO::fromArray($data);

        $this->assertEquals('Teste', $dto->descricao);
        $this->assertEquals(2000.00, $dto->valorTotal);
        $this->assertEquals(200.00, $dto->desconto);
        $this->assertEquals(50.00, $dto->taxas);
        $this->assertEquals('boleto', $dto->formaPagamento);
    }

    public function test_to_array_filtra_nulos(): void
    {
        $dto = new AtualizarOrcamentoDTO(
            descricao: 'Teste',
            valorTotal: 1000.00
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('descricao', $array);
        $this->assertArrayHasKey('valor_total', $array);
        $this->assertArrayNotHasKey('desconto', $array);
    }
}
