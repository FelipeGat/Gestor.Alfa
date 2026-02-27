<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissaoId = DB::table('permissoes')->where('recurso', 'categorias')->value('id');

        if (! $permissaoId) {
            DB::table('permissoes')->insert([
                'recurso' => 'categorias',
                'descricao' => 'Categorias Financeiras',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permissaoId = DB::table('permissoes')->where('recurso', 'categorias')->value('id');
        }

        $perfilPermissaoHasTimestamps = Schema::hasColumns('perfil_permissao', ['created_at', 'updated_at']);

        $perfis = DB::table('perfis')->get();
        foreach ($perfis as $perfil) {
            $values = [
                'ler' => true,
                'incluir' => true,
                'imprimir' => false,
                'excluir' => true,
            ];

            if ($perfilPermissaoHasTimestamps) {
                $values['updated_at'] = now();
            }

            DB::table('perfil_permissao')->updateOrInsert(
                [
                    'perfil_id' => $perfil->id,
                    'permissao_id' => $permissaoId,
                ],
                $values
            );

            if ($perfilPermissaoHasTimestamps) {
                DB::table('perfil_permissao')
                    ->where('perfil_id', $perfil->id)
                    ->where('permissao_id', $permissaoId)
                    ->whereNull('created_at')
                    ->update(['created_at' => now()]);
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
