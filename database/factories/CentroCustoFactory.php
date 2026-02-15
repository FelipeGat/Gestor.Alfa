<?php

namespace Database\Factories;

use App\Models\CentroCusto;
use Illuminate\Database\Eloquent\Factories\Factory;

class CentroCustoFactory extends Factory
{
    protected $model = CentroCusto::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->word(),
            'descricao' => $this->faker->sentence(),
            'ativo' => true,
        ];
    }
}
