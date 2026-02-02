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
        if (!Schema::hasTable('movimentacoes_financeiras')) {
            Schema::create('movimentacoes_financeiras', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conta_origem_id')->nullable()->constrained('contas_financeiras')->nullOnDelete();
                $table->foreignId('conta_destino_id')->nullable()->constrained('contas_financeiras')->nullOnDelete();
                $table->enum('tipo', [
                    'ajuste_entrada',
                    'ajuste_saida',
                    'transferencia',
                    'injeção_receita'
                ]);
                $table->decimal('valor', 15, 2);
                $table->decimal('saldo_resultante', 15, 2)->nullable();
                $table->text('observacao')->nullable();
                $table->foreignId('user_id')->constrained('users');
                $table->timestamp('data_movimentacao')->useCurrent();
                $table->timestamps();
                $table->softDeletes();
            });
        } else if (!Schema::hasColumn('movimentacoes_financeiras', 'saldo_resultante')) {
            Schema::table('movimentacoes_financeiras', function (Blueprint $table) {
                $table->decimal('saldo_resultante', 15, 2)->nullable()->after('valor');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('movimentacoes_financeiras')) {
            Schema::dropIfExists('movimentacoes_financeiras');
        }
    }
};
