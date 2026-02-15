<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class FornecedorFactory extends Factory
{
    protected $model = Fornecedor::class;

    public function definition(): array
    {
        return [
            'razao_social' => $this->faker->company(),
            'nome_fantasia' => $this->faker->companySuffix(),
            'cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'),
            'email' => $this->faker->unique()->safeEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'ativo' => true,
        ];
    }
}
