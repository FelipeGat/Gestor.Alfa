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
        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_custo_id')->constrained('centros_custo')->cascadeOnDelete();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->foreignId('conta_financeira_id')->nullable()->constrained('contas_financeiras')->nullOnDelete();
            $table->foreignId('conta_fixa_pagar_id')->nullable()->constrained('contas_fixas_pagar')->nullOnDelete();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->enum('status', ['em_aberto', 'pago', 'vencido'])->default('em_aberto');
            $table->enum('tipo', ['avulsa', 'fixa'])->default('avulsa');
            $table->datetime('pago_em')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_pagar');
    }
};
