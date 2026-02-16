<?php

namespace Tests\Unit\Actions;

use App\Actions\DTO\BaixarCobrancaDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BaixarCobrancaDTOTest extends TestCase
{
    public function test_criar_dto_com_dados_validos(): void
    {
        $dto = new BaixarCobrancaDTO(
            cobrancaId: 1,
            valor: 100.00,
            dataPagamento: '2024-01-15',
            formaPagamento: 'dinheiro',
            contaFinanceiraId: 1,
            usuarioId: 1
        );

        $this->assertEquals(1, $dto->cobrancaId);
        $this->assertEquals(100.00, $dto->valor);
        $this->assertEquals('dinheiro', $dto->formaPagamento);
    }

    public function test_criar_dto_sem_cobranca_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cobrança é obrigatória');

        new BaixarCobrancaDTO(
            cobrancaId: 0,
            valor: 100.00,
            dataPagamento: '2024-01-15',
            formaPagamento: 'dinheiro',
            contaFinanceiraId: 1,
            usuarioId: 1
        );
    }

    public function test_criar_dto_com_valor_negativo_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor deve ser maior que zero');

        new BaixarCobrancaDTO(
            cobrancaId: 1,
            valor: -50.00,
            dataPagamento: '2024-01-15',
            formaPagamento: 'dinheiro',
            contaFinanceiraId: 1,
            usuarioId: 1
        );
    }

    public function test_criar_dto_sem_forma_pagamento_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Forma de pagamento é obrigatória');

        new BaixarCobrancaDTO(
            cobrancaId: 1,
            valor: 100.00,
            dataPagamento: '2024-01-15',
            formaPagamento: '',
            contaFinanceiraId: 1,
            usuarioId: 1
        );
    }

    public function test_from_array_cria_dto_corretamente(): void
    {
        $data = [
            'cobranca_id' => 5,
            'valor' => 250.00,
            'data_pagamento' => '2024-02-01',
            'forma_pagamento' => 'transferencia',
            'conta_financeira_id' => 3,
            'usuario_id' => 10,
            'observacoes' => 'Teste',
        ];

        $dto = BaixarCobrancaDTO::fromArray($data);

        $this->assertEquals(5, $dto->cobrancaId);
        $this->assertEquals(250.00, $dto->valor);
        $this->assertEquals('transferencia', $dto->formaPagamento);
        $this->assertEquals(3, $dto->contaFinanceiraId);
        $this->assertEquals(10, $dto->usuarioId);
        $this->assertEquals('Teste', $dto->observacoes);
    }
}
