<?php

namespace Database\Seeders;

use App\Models\Funcionario;
use App\Models\Perfil;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FuncionarioTecnicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca o perfil Técnico
        $perfilTecnico = Perfil::where('slug', 'funcionario')->first();

        if (! $perfilTecnico) {
            $this->command->error('Perfil "funcionario" não encontrado. Execute o seeder de perfis primeiro.');
            return;
        }

        // Cria o funcionário
        $funcionario = Funcionario::create([
            'nome' => 'João Técnico',
            'ativo' => true,
        ]);

        // Cria o usuário vinculado ao funcionário
        $usuario = User::create([
            'name' => 'João Técnico',
            'email' => 'joao.tecnico@gestoralfa.com.br',
            'password' => Hash::make('123456'),
            'tipo' => 'funcionario',
            'funcionario_id' => $funcionario->id,
            'primeiro_acesso' => true,
            'email_verified_at' => now(),
        ]);

        // Vincula o perfil Técnico ao usuário
        $usuario->perfis()->sync([$perfilTecnico->id]);

        $this->command->info('Usuário funcionário técnico criado com sucesso!');
        $this->command->info('Email: joao.tecnico@gestoralfa.com.br');
        $this->command->info('Senha: 123456');
    }
}
