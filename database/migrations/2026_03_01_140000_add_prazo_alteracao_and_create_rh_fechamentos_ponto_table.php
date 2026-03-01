<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jornadas')) {
            Schema::table('jornadas', function (Blueprint $table) {
                if (!Schema::hasColumn('jornadas', 'dias_permitidos_alteracao_apos_fechamento')) {
                    $table->unsignedSmallInteger('dias_permitidos_alteracao_apos_fechamento')
                        ->default(0)
                        ->after('permitir_ponto_fora_horario');
                }
            });
        }

        if (!Schema::hasTable('rh_fechamentos_ponto')) {
            Schema::create('rh_fechamentos_ponto', function (Blueprint $table) {
                $table->id();
                $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
                $table->date('competencia');
                $table->timestamp('fechado_em');
                $table->foreignId('fechado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['funcionario_id', 'competencia'], 'uk_rh_fechamento_ponto_func_comp');
                $table->index(['competencia', 'funcionario_id'], 'idx_rh_fechamento_ponto_comp_func');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rh_fechamentos_ponto')) {
            Schema::dropIfExists('rh_fechamentos_ponto');
        }

        if (Schema::hasTable('jornadas')) {
            Schema::table('jornadas', function (Blueprint $table) {
                if (Schema::hasColumn('jornadas', 'dias_permitidos_alteracao_apos_fechamento')) {
                    $table->dropColumn('dias_permitidos_alteracao_apos_fechamento');
                }
            });
        }
    }
};
