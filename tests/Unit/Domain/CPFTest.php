<?php

namespace Tests\Unit\Domain;

use App\Domain\ValueObjects\CPF;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CPFTest extends TestCase
{
    public function test_criar_cpf_valido(): void
    {
        $cpf = new CPF('123.456.789-00');

        $this->assertEquals('12345678900', $cpf->getNumero());
    }

    public function test_criar_cpf_com_digitos_invalidos_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF inválido');

        new CPF('123.456.789-99');
    }

    public function test_criar_cpf_com_menos_de_11_digitos_deve_lançar_exceção(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF deve ter 11 dígitos');

        new CPF('12345678');
    }

    public function test_cpf_com_todos_digitos_iguais_e_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CPF('111.111.111-11');
    }

    public function test_formatar_cpf(): void
    {
        $cpf = new CPF('12345678900');

        $this->assertEquals('123.456.789-00', $cpf->formatado());
    }

    public function test_to_string(): void
    {
        $cpf = new CPF('12345678900');

        $this->assertEquals('123.456.789-00', (string) $cpf);
    }

    public function test_cpf_valido_oficial(): void
    {
        $cpf = new CPF('529.982.247-25');

        $this->assertEquals('52998224725', $cpf->getNumero());
    }
}
