<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('permissoes')->where('recurso', 'categorias')->exists();

        if (! $exists) {
            DB::table('permissoes')->insert([
                'recurso' => 'categorias',
                'descricao' => 'Categorias Financeiras',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permissaoId = DB::table('permissoes')->where('recurso', 'categorias')->first()->id;

            $perfis = DB::table('perfis')->get();
            foreach ($perfis as $perfil) {
                DB::table('perfil_permissao')->insert([
                    'perfil_id' => $perfil->id,
                    'permissao_id' => $permissaoId,
                    'ler' => true,
                    'incluir' => true,
                    'imprimir' => false,
                    'excluir' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissao = DB::table('permissoes')->where('recurso', 'categorias')->first();
        if ($permissao) {
            DB::table('perfil_permissao')->where('permissao_id', $permissao->id)->delete();
            DB::table('permissoes')->where('id', $permissao->id)->delete();
        }
    }
};
