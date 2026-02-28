<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jornadas', function (Blueprint $table) {
            if (!Schema::hasColumn('jornadas', 'tipo_jornada')) {
                $table->string('tipo_jornada')->default('fixa')->after('nome');
            }

            if (!Schema::hasColumn('jornadas', 'dias_trabalhados')) {
                $table->json('dias_trabalhados')->nullable()->after('tipo_jornada');
            }

            if (!Schema::hasColumn('jornadas', 'hora_entrada_padrao')) {
                $table->time('hora_entrada_padrao')->nullable()->after('dias_trabalhados');
            }

            if (!Schema::hasColumn('jornadas', 'hora_saida_padrao')) {
                $table->time('hora_saida_padrao')->nullable()->after('hora_entrada_padrao');
            }

            if (!Schema::hasColumn('jornadas', 'tolerancia_entrada_min')) {
                $table->unsignedSmallInteger('tolerancia_entrada_min')->default(0)->after('carga_horaria_semanal');
            }

            if (!Schema::hasColumn('jornadas', 'tolerancia_saida_min')) {
                $table->unsignedSmallInteger('tolerancia_saida_min')->default(0)->after('tolerancia_entrada_min');
            }

            if (!Schema::hasColumn('jornadas', 'tolerancia_intervalo_min')) {
                $table->unsignedSmallInteger('tolerancia_intervalo_min')->default(0)->after('tolerancia_saida_min');
            }

            if (!Schema::hasColumn('jornadas', 'minimo_horas_para_extra')) {
                $table->unsignedSmallInteger('minimo_horas_para_extra')->default(0)->after('tolerancia_intervalo_min');
            }

            if (!Schema::hasColumn('jornadas', 'permitir_ponto_fora_horario')) {
                $table->boolean('permitir_ponto_fora_horario')->default(true)->after('minimo_horas_para_extra');
            }

            if (!Schema::hasColumn('jornadas', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('permitir_ponto_fora_horario');
            }
        });

        DB::statement("UPDATE jornadas SET tipo_jornada = COALESCE(tipo_jornada, 'fixa')");
        DB::statement("UPDATE jornadas SET dias_trabalhados = '[1,2,3,4,5]' WHERE dias_trabalhados IS NULL");
        DB::statement("UPDATE jornadas SET hora_entrada_padrao = hora_inicio WHERE hora_entrada_padrao IS NULL");
        DB::statement("UPDATE jornadas SET hora_saida_padrao = hora_fim WHERE hora_saida_padrao IS NULL");

        Schema::create('jornadas_escala', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jornada_id')->constrained('jornadas')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_entrada');
            $table->time('hora_saida');
            $table->unsignedSmallInteger('intervalo_minutos')->default(0);
            $table->decimal('carga_horaria_dia', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['jornada_id', 'dia_semana'], 'uk_jornada_escala_dia');
        });

        Schema::create('feriados', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->date('data');
            $table->string('tipo')->default('nacional');
            $table->boolean('recorrente_anual')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['data', 'ativo'], 'idx_feriados_data_ativo');
        });

        Schema::create('feriado_jornada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jornada_id')->constrained('jornadas')->cascadeOnDelete();
            $table->foreignId('feriado_id')->constrained('feriados')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['jornada_id', 'feriado_id'], 'uk_feriado_jornada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriado_jornada');
        Schema::dropIfExists('feriados');
        Schema::dropIfExists('jornadas_escala');

        Schema::table('jornadas', function (Blueprint $table) {
            foreach ([
                'tipo_jornada',
                'dias_trabalhados',
                'hora_entrada_padrao',
                'hora_saida_padrao',
                'tolerancia_entrada_min',
                'tolerancia_saida_min',
                'tolerancia_intervalo_min',
                'minimo_horas_para_extra',
                'permitir_ponto_fora_horario',
                'ativo',
            ] as $coluna) {
                if (Schema::hasColumn('jornadas', $coluna)) {
                    $table->dropColumn($coluna);
                }
            }
        });
    }
};
