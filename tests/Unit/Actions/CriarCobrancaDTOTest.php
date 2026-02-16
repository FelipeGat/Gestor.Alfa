<?php

namespace Tests\Unit\Actions;

use App\Actions\DTO\CriarCobrancaDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CriarCobrancaDTOTest extends TestCase
{
    public function test_criar_dto_com_dados_validos(): void
    {
        $dto = new CriarCobrancaDTO(
            orcamentoId: 1,
            valor: 1000.00,
            dataVencimento: '2024-12-31'
        );

        $this->assertEquals(1, $dto->orcamentoId);
        $this->assertEquals(1000.00, $dto->valor);
        $this->assertEquals('2024-12-31', $dto->dataVencimento);
    }

    public function test_criar_dto_sem_orcamento_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Orçamento é obrigatório');

        new CriarCobrancaDTO(
            orcamentoId: 0,
            valor: 1000.00,
            dataVencimento: '2024-12-31'
        );
    }

    public function test_criar_dto_com_valor_negativo_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor deve ser maior que zero');

        new CriarCobrancaDTO(
            orcamentoId: 1,
            valor: -100.00,
            dataVencimento: '2024-12-31'
        );
    }

    public function test_criar_dto_sem_data_vencimento_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data de vencimento é obrigatória');

        new CriarCobrancaDTO(
            orcamentoId: 1,
            valor: 1000.00,
            dataVencimento: ''
        );
    }

    public function test_from_array_cria_dto_corretamente(): void
    {
        $data = [
            'orcamento_id' => 5,
            'valor' => 2500.00,
            'data_vencimento' => '2024-06-15',
            'descricao' => 'Teste',
            'gerar_boleto' => true,
            'usuario_id' => 10,
        ];

        $dto = CriarCobrancaDTO::fromArray($data);

        $this->assertEquals(5, $dto->orcamentoId);
        $this->assertEquals(2500.00, $dto->valor);
        $this->assertEquals('Teste', $dto->descricao);
        $this->assertTrue($dto->gerarBoleto);
    }
}
