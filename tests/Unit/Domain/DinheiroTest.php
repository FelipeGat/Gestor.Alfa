<?php

namespace Tests\Unit\Domain;

use App\Domain\ValueObjects\Dinheiro;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DinheiroTest extends TestCase
{
    public function test_criar_dinheiro_com_valor_valido(): void
    {
        $dinheiro = new Dinheiro(100.50);

        $this->assertEquals(100.50, $dinheiro->getValor());
        $this->assertEquals('BRL', $dinheiro->getMoeda());
    }

    public function test_criar_dinheiro_com_valor_negativo_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor monetário não pode ser negativo');

        new Dinheiro(-50);
    }

    public function test_formatar_dinheiro_com_simbolo(): void
    {
        $dinheiro = new Dinheiro(1500.50);

        $this->assertEquals('R$ 1.500,50', $dinheiro->formatado());
    }

    public function test_formatar_dinheiro_sem_simbolo(): void
    {
        $dinheiro = new Dinheiro(1500.50);

        $this->assertEquals('1.500,50', $dinheiro->formatado(false));
    }

    public function test_somar_dois_dinheiros(): void
    {
        $dinheiro1 = new Dinheiro(100);
        $dinheiro2 = new Dinheiro(50);

        $resultado = $dinheiro1->somar($dinheiro2);

        $this->assertEquals(150, $resultado->getValor());
    }

    public function test_subtrair_dois_dinheiros(): void
    {
        $dinheiro1 = new Dinheiro(100);
        $dinheiro2 = new Dinheiro(30);

        $resultado = $dinheiro1->subtrair($dinheiro2);

        $this->assertEquals(70, $resultado->getValor());
    }

    public function test_subtrair_maior_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $dinheiro1 = new Dinheiro(30);
        $dinheiro2 = new Dinheiro(100);

        $dinheiro1->subtrair($dinheiro2);
    }

    public function test_multiplicar_dinheiro(): void
    {
        $dinheiro = new Dinheiro(100);

        $resultado = $dinheiro->multiplicar(1.1);

        $this->assertEquals(110, $resultado->getValor());
    }

    public function test_calcular_percentual(): void
    {
        $dinheiro = new Dinheiro(100);

        $resultado = $dinheiro->percentual(10);

        $this->assertEquals(10, $resultado->getValor());
    }

    public function test_comparar_dinheiros(): void
    {
        $dinheiro1 = new Dinheiro(100);
        $dinheiro2 = new Dinheiro(50);
        $dinheiro3 = new Dinheiro(100);

        $this->assertTrue($dinheiro1->ehMaiorQue($dinheiro2));
        $this->assertTrue($dinheiro2->ehMenorQue($dinheiro1));
        $this->assertTrue($dinheiro1->ehIgualA($dinheiro3));
    }

    public function test_criar_dinheiro_from_float(): void
    {
        $dinheiro = Dinheiro::fromFloat(250.75);

        $this->assertEquals(250.75, $dinheiro->getValor());
    }

    public function test_criar_dinheiro_from_string(): void
    {
        $dinheiro = Dinheiro::fromString('R$ 1.250,50');

        $this->assertEquals(1250.50, $dinheiro->getValor());
    }

    public function test_to_string(): void
    {
        $dinheiro = new Dinheiro(100);

        $this->assertEquals('R$ 100,00', (string) $dinheiro);
    }
}
