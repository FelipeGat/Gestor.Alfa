<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona campos de parcelamento em cartão de crédito à tabela contas_pagar.
     * Campos: cartao_credito_id (FK), parcela_num, parcelas_total.
     */
    public function up(): void
    {
        if (!Schema::hasTable('contas_pagar')) {
            return;
        }

        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->foreignId('cartao_credito_id')
                ->nullable()
                ->after('conta_financeira_id')
                ->constrained('contas_financeiras')
                ->nullOnDelete()
                ->comment('Cartão de crédito usado no lançamento parcelado');
            $table->unsignedSmallInteger('parcela_num')->nullable()->after('cartao_credito_id')
                ->comment('Número da parcela atual (ex.: 1, 2, 3)');
            $table->unsignedSmallInteger('parcelas_total')->nullable()->after('parcela_num')
                ->comment('Total de parcelas do lançamento (ex.: 12)');
        });
    }

    public function down(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropForeign(['cartao_credito_id']);
            $table->dropColumn(['cartao_credito_id', 'parcela_num', 'parcelas_total']);
        });
    }
};
