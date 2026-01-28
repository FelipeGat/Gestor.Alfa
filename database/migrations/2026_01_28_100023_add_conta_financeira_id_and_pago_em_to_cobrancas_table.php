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
        Schema::table('cobrancas', function (Blueprint $table) {
            // Adiciona conta_financeira_id (banco onde foi recebido)
            if (!Schema::hasColumn('cobrancas', 'conta_financeira_id')) {
                $table->foreignId('conta_financeira_id')
                    ->nullable()
                    ->after('cliente_id')
                    ->constrained('contas_financeiras')
                    ->nullOnDelete();
            }

            // Adiciona pago_em (data/hora do pagamento)
            if (!Schema::hasColumn('cobrancas', 'pago_em')) {
                $table->timestamp('pago_em')
                    ->nullable()
                    ->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (Schema::hasColumn('cobrancas', 'pago_em')) {
                $table->dropColumn('pago_em');
            }

            if (Schema::hasColumn('cobrancas', 'conta_financeira_id')) {
                $table->dropForeign(['conta_financeira_id']);
                $table->dropColumn('conta_financeira_id');
            }
        });
    }
};
