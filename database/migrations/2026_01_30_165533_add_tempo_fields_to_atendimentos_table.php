<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            // Controle de tempo de execução
            $table->timestamp('iniciado_em')->nullable()->after('data_atendimento');
            $table->timestamp('finalizado_em')->nullable()->after('iniciado_em');
            $table->integer('tempo_execucao_segundos')->default(0)->after('finalizado_em'); // Tempo efetivo trabalhado
            $table->integer('tempo_pausa_segundos')->default(0)->after('tempo_execucao_segundos'); // Tempo total pausado
            $table->boolean('em_execucao')->default(false)->after('tempo_pausa_segundos'); // Flag se está rodando agora
            $table->boolean('em_pausa')->default(false)->after('em_execucao'); // Flag se está em pausa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropColumn([
                'iniciado_em',
                'finalizado_em',
                'tempo_execucao_segundos',
                'tempo_pausa_segundos',
                'em_execucao',
                'em_pausa'
            ]);
        });
    }
};
