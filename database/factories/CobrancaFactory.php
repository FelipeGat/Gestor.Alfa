<?php

namespace Database\Factories;

use App\Models\Cobranca;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\ContaFixa;
use Illuminate\Database\Eloquent\Factories\Factory;

class CobrancaFactory extends Factory
{
    protected $model = Cobranca::class;

    public function definition(): array
    {
        return [
            'descricao' => $this->faker->sentence(3),
            'valor' => $this->faker->randomFloat(2, 100, 10000),
            'data_vencimento' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => 'em_aberto',
            'tipo' => 'orcamento',
            'cliente_id' => null,
            'orcamento_id' => null,
            'conta_fixa_id' => null,
        ];
    }

    public function pago(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pago',
            'data_pagamento' => now(),
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_vencimento' => now()->subDays(5),
        ]);
    }
}
