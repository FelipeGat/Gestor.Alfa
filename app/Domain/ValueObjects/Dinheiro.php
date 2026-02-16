<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class Dinheiro
{
    private float $valor;

    private string $moeda;

    public function __construct(float $valor, string $moeda = 'BRL')
    {
        if ($valor < 0) {
            throw new InvalidArgumentException('Valor monetário não pode ser negativo');
        }

        $this->valor = round($valor, 2);
        $this->moeda = $moeda;
    }

    public static function fromFloat(float $valor, string $moeda = 'BRL'): self
    {
        return new self($valor, $moeda);
    }

    public static function fromString(string $valor, string $moeda = 'BRL'): self
    {
        $valorLimpo = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], trim($valor));

        return new self((float) $valorLimpo, $moeda);
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getMoeda(): string
    {
        return $this->moeda;
    }

    public function formatado(bool $simbolo = true): string
    {
        $valorFormatado = number_format($this->valor, 2, ',', '.');

        if ($simbolo) {
            return "R$ {$valorFormatado}";
        }

        return $valorFormatado;
    }

    public function somar(Dinheiro $outro): Dinheiro
    {
        return new self($this->valor + $outro->valor, $this->moeda);
    }

    public function subtrair(Dinheiro $outro): Dinheiro
    {
        $resultado = $this->valor - $outro->valor;

        if ($resultado < 0) {
            throw new InvalidArgumentException('Resultado da subtração não pode ser negativo');
        }

        return new self($resultado, $this->moeda);
    }

    public function multiplicar(float $fator): Dinheiro
    {
        return new self($this->valor * $fator, $this->moeda);
    }

    public function percentual(float $porcentagem): Dinheiro
    {
        return new self($this->valor * ($porcentagem / 100), $this->moeda);
    }

    public function ehMaiorQue(Dinheiro $outro): bool
    {
        return $this->valor > $outro->valor;
    }

    public function ehMenorQue(Dinheiro $outro): bool
    {
        return $this->valor < $outro->valor;
    }

    public function ehIgualA(Dinheiro $outro): bool
    {
        return $this->valor === $outro->valor && $this->moeda === $outro->moeda;
    }

    public function __toString(): string
    {
        return $this->formatado();
    }
}
