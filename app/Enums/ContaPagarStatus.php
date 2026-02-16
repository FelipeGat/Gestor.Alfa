<?php

namespace App\Enums;

enum ContaPagarStatus: string
{
    case PENDENTE = 'pendente';
    case PAGO = 'pago';
    case VENCIDO = 'vencido';
    case CANCELADO = 'cancelado';
    case PARCIAL = 'parcial';

    public function label(): string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::PAGO => 'Pago',
            self::VENCIDO => 'Vencido',
            self::CANCELADO => 'Cancelado',
            self::PARCIAL => 'Parcial',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isPago(): bool
    {
        return $this === self::PAGO;
    }

    public function isPendente(): bool
    {
        return $this === self::PENDENTE;
    }

    public function isVencido(): bool
    {
        return $this === self::VENCIDO;
    }
}
