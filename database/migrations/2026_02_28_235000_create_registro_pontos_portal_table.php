<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_pontos_portal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->date('data_referencia');
            $table->timestamp('entrada_em')->nullable();
            $table->timestamp('intervalo_inicio_em')->nullable();
            $table->timestamp('intervalo_fim_em')->nullable();
            $table->timestamp('saida_em')->nullable();
            $table->foreignId('registrado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->unique(['funcionario_id', 'data_referencia'], 'uk_registro_pontos_portal_func_data');
            $table->index(['data_referencia', 'funcionario_id'], 'idx_registro_pontos_portal_data_func');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_pontos_portal');
    }
};
