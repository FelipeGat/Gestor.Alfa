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
        // Adicionar campo para registrar quem iniciou e finalizou o atendimento
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->foreignId('iniciado_por_user_id')->nullable()->after('iniciado_em')->constrained('users')->onDelete('set null');
            $table->foreignId('finalizado_por_user_id')->nullable()->after('finalizado_em')->constrained('users')->onDelete('set null');
        });

        // Adicionar campo para registrar quem retomou a pausa
        Schema::table('atendimento_pausas', function (Blueprint $table) {
            $table->foreignId('retomado_por_user_id')->nullable()->after('encerrada_em')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropForeign(['iniciado_por_user_id']);
            $table->dropForeign(['finalizado_por_user_id']);
            $table->dropColumn(['iniciado_por_user_id', 'finalizado_por_user_id']);
        });

        Schema::table('atendimento_pausas', function (Blueprint $table) {
            $table->dropForeign(['retomado_por_user_id']);
            $table->dropColumn('retomado_por_user_id');
        });
    }
};
