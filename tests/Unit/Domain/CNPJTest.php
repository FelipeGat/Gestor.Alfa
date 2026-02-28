<?php

namespace Tests\Unit\Domain;

use App\Domain\ValueObjects\CNPJ;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CNPJTest extends TestCase
{
    public function test_criar_cnpj_valido(): void
    {
        $cnpj = new CNPJ('04.252.011/0001-10');

        $this->assertEquals('04252011000110', $cnpj->getNumero());
    }

    public function test_criar_cnpj_com_digitos_invalidos_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ inválido');

        new CNPJ('12.345.678/0001-99');
    }

    public function test_criar_cnpj_com_menos_de_14_digitos_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ deve ter 14 dígitos');

        new CNPJ('123456780001');
    }

    public function test_cnpj_com_todos_digitos_iguais_e_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CNPJ('11.111.111/1111-11');
    }

    public function test_formatar_cnpj(): void
    {
        $cnpj = new CNPJ('04252011000110');

        $this->assertEquals('04.252.011/0001-10', $cnpj->formatado());
    }

    public function test_to_string(): void
    {
        $cnpj = new CNPJ('04252011000110');

        $this->assertEquals('04.252.011/0001-10', (string) $cnpj);
    }

    public function test_cnpj_valido_oficial(): void
    {
        $cnpj = new CNPJ('04.252.011/0001-10');

        $this->assertEquals('04252011000110', $cnpj->getNumero());
    }
}
