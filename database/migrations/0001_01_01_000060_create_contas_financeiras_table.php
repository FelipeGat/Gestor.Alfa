<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nome');
            $table->enum('tipo', ['CONTA_CORRENTE', 'POUPANCA', 'CARTAO_CREDITO', 'CAIXA'])->default('CONTA_CORRENTE');
            $table->decimal('limite_credito', 15, 2)->nullable();
            $table->decimal('limite_credito_utilizado', 15, 2)->default(0);
            $table->decimal('limite_cheque_especial', 15, 2)->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_financeiras');
    }
};
