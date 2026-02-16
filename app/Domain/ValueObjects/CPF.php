<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class CPF
{
    private string $numero;

    public function __construct(string $cpf)
    {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpfLimpo) !== 11) {
            throw new InvalidArgumentException('CPF deve ter 11 dígitos');
        }

        if (! $this->validar($cpfLimpo)) {
            throw new InvalidArgumentException('CPF inválido');
        }

        $this->numero = $cpfLimpo;
    }

    private function validar(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $digito = 0;
            for ($c = 0; $c < $t; $c++) {
                $digito += $cpf[$c] * (($t + 1) - $c);
            }
            $digito = ((10 * $digito) % 11) % 10;
            if ($cpf[$c] != $digito) {
                return false;
            }
        }

        return true;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function formatado(): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->numero);
    }

    public function __toString(): string
    {
        return $this->formatado();
    }
}
