<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CentroCusto;
use App\Models\Empresa;

class CentroCustoSeeder extends Seeder
{
    public function run(): void
    {
        // Centro de Custo GRUPO (sem empresa específica)
        CentroCusto::create([
            'nome' => 'Grupo - Despesas Gerais',
            'tipo' => 'GRUPO',
            'empresa_id' => null,
            'ativo' => true,
        ]);

        // Criar centros de custo para cada empresa cadastrada
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            CentroCusto::create([
                'nome' => $empresa->nome_fantasia,
                'tipo' => 'CNPJ',
                'empresa_id' => $empresa->id,
                'ativo' => true,
            ]);
        }

        $this->command->info('✅ Centros de custo criados com sucesso!');
    }
}
