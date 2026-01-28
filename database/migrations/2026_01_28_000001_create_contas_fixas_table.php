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
        Schema::create('contas_fixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('categoria')->default('Contratos'); // Pré-fixado como "Contratos"
            $table->decimal('valor', 10, 2);
            $table->foreignId('conta_financeira_id')->constrained('contas_financeiras')->cascadeOnDelete();
            $table->enum('forma_pagamento', ['pix', 'boleto', 'cartao_credito', 'cartao_debito', 'faturado']);
            $table->enum('periodicidade', ['diaria', 'semanal', 'quinzenal', 'mensal', 'semestral', 'anual']);
            $table->date('data_inicial');
            $table->date('data_fim')->nullable(); // Se não marcado, será null (para sempre)
            $table->decimal('percentual_renovacao', 5, 2)->nullable(); // % de renovação
            $table->date('data_atualizacao_percentual')->nullable(); // Quando ocorre a renovação
            $table->text('observacao')->nullable();
            $table->boolean('ativo')->default(true); // Para controlar se a conta fixa está ativa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_fixas');
    }
};
