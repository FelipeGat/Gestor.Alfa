<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Conta;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        // ============== DESPESAS FIXAS ==============
        $fixas = Categoria::create([
            'nome' => 'Despesas Fixas',
            'tipo' => 'FIXA',
            'ativo' => true,
        ]);

        // Escritório
        $escritorio = Subcategoria::create([
            'categoria_id' => $fixas->id,
            'nome' => 'Escritório',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $escritorio->id, 'nome' => 'Aluguel', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $escritorio->id, 'nome' => 'Água', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $escritorio->id, 'nome' => 'Energia Elétrica', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $escritorio->id, 'nome' => 'Internet', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $escritorio->id, 'nome' => 'Telefonia', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Pessoal
        $pessoal = Subcategoria::create([
            'categoria_id' => $fixas->id,
            'nome' => 'Pessoal',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $pessoal->id, 'nome' => 'Salários', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $pessoal->id, 'nome' => 'Pró-labore', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $pessoal->id, 'nome' => 'INSS', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $pessoal->id, 'nome' => 'FGTS', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $pessoal->id, 'nome' => 'Vale Transporte', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $pessoal->id, 'nome' => 'Vale Alimentação', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Financeiro
        $financeiro = Subcategoria::create([
            'categoria_id' => $fixas->id,
            'nome' => 'Financeiro',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $financeiro->id, 'nome' => 'Honorários Contábeis', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $financeiro->id, 'nome' => 'Taxas Bancárias', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $financeiro->id, 'nome' => 'Sistema / ERP', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Frota (Fixo)
        $frotaFixo = Subcategoria::create([
            'categoria_id' => $fixas->id,
            'nome' => 'Frota (Fixo)',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $frotaFixo->id, 'nome' => 'Seguro Veicular', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $frotaFixo->id, 'nome' => 'IPVA', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $frotaFixo->id, 'nome' => 'Rastreamento', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ============== DESPESAS VARIÁVEIS ==============
        $variaveis = Categoria::create([
            'nome' => 'Despesas Variáveis',
            'tipo' => 'VARIAVEL',
            'ativo' => true,
        ]);

        // Escritório / Infraestrutura
        $escritorioInfra = Subcategoria::create([
            'categoria_id' => $variaveis->id,
            'nome' => 'Escritório / Infraestrutura',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $escritorioInfra->id, 'nome' => 'Reparos Hidráulicos', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $escritorioInfra->id, 'nome' => 'Reparos Elétricos', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $escritorioInfra->id, 'nome' => 'Manutenção Predial', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Frota (Variável)
        $frotaVariavel = Subcategoria::create([
            'categoria_id' => $variaveis->id,
            'nome' => 'Frota (Variável)',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $frotaVariavel->id, 'nome' => 'Combustível', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $frotaVariavel->id, 'nome' => 'Manutenção Veicular', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $frotaVariavel->id, 'nome' => 'Pedágio / Estacionamento', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Operacional
        $operacional = Subcategoria::create([
            'categoria_id' => $variaveis->id,
            'nome' => 'Operacional',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $operacional->id, 'nome' => 'Materiais Elétricos', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $operacional->id, 'nome' => 'Materiais de CFTV', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $operacional->id, 'nome' => 'Materiais de Ar-Condicionado', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $operacional->id, 'nome' => 'Ferramentas', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Comercial
        $comercial = Subcategoria::create([
            'categoria_id' => $variaveis->id,
            'nome' => 'Comercial',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $comercial->id, 'nome' => 'Comissões', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $comercial->id, 'nome' => 'Deslocamento Comercial', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Impostos
        $impostos = Subcategoria::create([
            'categoria_id' => $variaveis->id,
            'nome' => 'Impostos',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $impostos->id, 'nome' => 'ISS', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $impostos->id, 'nome' => 'Simples Nacional', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ============== INVESTIMENTOS ==============
        $investimentos = Categoria::create([
            'nome' => 'Investimentos',
            'tipo' => 'INVESTIMENTO',
            'ativo' => true,
        ]);

        // Estrutura
        $estrutura = Subcategoria::create([
            'categoria_id' => $investimentos->id,
            'nome' => 'Estrutura',
            'ativo' => true,
        ]);
        Conta::insert([
            ['subcategoria_id' => $estrutura->id, 'nome' => 'Compra de Equipamentos', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['subcategoria_id' => $estrutura->id, 'nome' => 'Compra de Ferramentas', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('✅ Categorias, subcategorias e contas criadas com sucesso!');
    }
}
