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
        Schema::create('movimentacoes_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_origem_id')->nullable()->constrained('contas_financeiras')->nullOnDelete();
            $table->foreignId('conta_destino_id')->nullable()->constrained('contas_financeiras')->nullOnDelete();
            $table->enum('tipo', [
                'ajuste_entrada', // ajuste manual de entrada
                'ajuste_saida',   // ajuste manual de saída
                'transferencia',  // transferência entre contas
                'injeção_receita' // injeção manual de receita
            ]);
            $table->decimal('valor', 15, 2);
            $table->text('observacao')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('data_movimentacao')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_financeiras');
    }
};
