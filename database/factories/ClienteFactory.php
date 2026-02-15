<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'nome_fantasia' => $this->faker->company(),
            'razao_social' => $this->faker->company() . ' LTDA',
            'cpf_cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'),
            'email' => $this->faker->unique()->safeEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'ativo' => true,
        ];
    }
}
