<?php

namespace Database\Factories;

use App\Models\ContaPagar;
use App\Models\Fornecedor;
use App\Models\CentroCusto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContaPagarFactory extends Factory
{
    protected $model = ContaPagar::class;

    public function definition(): array
    {
        return [
            'descricao' => $this->faker->sentence(3),
            'valor' => $this->faker->randomFloat(2, 50, 5000),
            'data_vencimento' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => 'em_aberto',
            'tipo' => 'avulsa',
            'fornecedor_id' => null,
            'centro_custo_id' => null,
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
