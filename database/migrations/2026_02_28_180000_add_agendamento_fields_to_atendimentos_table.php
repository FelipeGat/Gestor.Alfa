<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->string('periodo_agendamento', 20)->nullable()->after('data_atendimento');
            $table->dateTime('data_inicio_agendamento')->nullable()->after('periodo_agendamento');
            $table->dateTime('data_fim_agendamento')->nullable()->after('data_inicio_agendamento');
            $table->unsignedSmallInteger('duracao_agendamento_minutos')->nullable()->after('data_fim_agendamento');

            $table->index(['funcionario_id', 'data_inicio_agendamento'], 'idx_agenda_tecnico_inicio');
            $table->index(['funcionario_id', 'data_fim_agendamento'], 'idx_agenda_tecnico_fim');
        });
    }

    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropIndex('idx_agenda_tecnico_inicio');
            $table->dropIndex('idx_agenda_tecnico_fim');

            $table->dropColumn([
                'periodo_agendamento',
                'data_inicio_agendamento',
                'data_fim_agendamento',
                'duracao_agendamento_minutos',
            ]);
        });
    }
};
