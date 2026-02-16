<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class CNPJ
{
    private string $numero;

    public function __construct(string $cnpj)
    {
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpjLimpo) !== 14) {
            throw new InvalidArgumentException('CNPJ deve ter 14 dígitos');
        }

        if (! $this->validar($cnpjLimpo)) {
            throw new InvalidArgumentException('CNPJ inválido');
        }

        $this->numero = $cnpjLimpo;
    }

    private function validar(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $soma1 = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma1 += $cnpj[$i] * $pesos1[$i];
        }
        $digito1 = $soma1 % 11 < 2 ? 0 : 11 - ($soma1 % 11);

        if ($cnpj[12] != $digito1) {
            return false;
        }

        $soma2 = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma2 += $cnpj[$i] * $pesos2[$i];
        }
        $digito2 = $soma2 % 11 < 2 ? 0 : 11 - ($soma2 % 11);

        return $cnpj[13] == $digito2;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function formatado(): string
    {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->numero);
    }

    public function __toString(): string
    {
        return $this->formatado();
    }
}
