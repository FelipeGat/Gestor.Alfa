<?php

namespace Database\Factories;

use App\Models\ContaFinanceira;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContaFinanceiraFactory extends Factory
{
    protected $model = ContaFinanceira::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->word() . ' ' . $this->faker->word(),
            'banco' => $this->faker->company(),
            'agencia' => $this->faker->numerify('####'),
            'conta' => $this->faker->numerify('######'),
            'saldo' => $this->faker->randomFloat(2, 0, 10000),
            'empresa_id' => null,
            'ativo' => true,
        ];
    }
}
