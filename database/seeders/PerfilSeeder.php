<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Illuminate\Database\Seeder;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $perfis = [
            ['nome' => 'Administrador', 'slug' => 'admin'],
            ['nome' => 'Administrativo', 'slug' => 'administrativo'],
            ['nome' => 'Financeiro', 'slug' => 'financeiro'],
            ['nome' => 'Comercial', 'slug' => 'comercial'],
            ['nome' => 'Técnico', 'slug' => 'tecnico'],
            ['nome' => 'Cliente', 'slug' => 'cliente'],
            ['nome' => 'Funcionário', 'slug' => 'funcionario'],
        ];

        foreach ($perfis as $perfil) {
            Perfil::firstOrCreate(
                ['slug' => $perfil['slug']],
                ['nome' => $perfil['nome']]
            );
        }
    }
}
