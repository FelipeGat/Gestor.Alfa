<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se a coluna 'alterar' já existe
        $hasAlterar = Schema::hasColumn('perfil_permissao', 'alterar');
        
        // Adicionar coluna 'alterar' se não existir
        if (!$hasAlterar) {
            Schema::table('perfil_permissao', function ($table) {
                $table->boolean('alterar')->default(false)->after('imprimir');
            });
        }

        $permissaoId = DB::table('permissoes')->where('recurso', 'equipamentos')->value('id');

        if (! $permissaoId) {
            DB::table('permissoes')->insert([
                'recurso' => 'equipamentos',
                'descricao' => 'Equipamentos',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permissaoId = DB::table('permissoes')->where('recurso', 'equipamentos')->value('id');
        }

        $perfilPermissaoHasTimestamps = Schema::hasColumns('perfil_permissao', ['created_at', 'updated_at']);

        $perfis = DB::table('perfis')->get();
        foreach ($perfis as $perfil) {
            $values = [
                'ler' => true,
                'incluir' => true,
                'imprimir' => false,
                'alterar' => true,
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
        $permissao = DB::table('permissoes')->where('recurso', 'equipamentos')->first();
        if ($permissao) {
            DB::table('perfil_permissao')->where('permissao_id', $permissao->id)->delete();
            DB::table('permissoes')->where('id', $permissao->id)->delete();
        }
        
        // Remover coluna 'alterar' se foi adicionada por esta migration
        if (Schema::hasColumn('perfil_permissao', 'alterar')) {
            Schema::table('perfil_permissao', function ($table) {
                $table->dropColumn('alterar');
            });
        }
    }
};
