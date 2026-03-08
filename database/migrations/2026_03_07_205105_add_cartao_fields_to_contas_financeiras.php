<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona campos específicos de cartão de crédito à tabela contas_financeiras.
     * Campos: bandeira, melhor_dia_compra, dia_fechamento_fatura, dia_vencimento_fatura.
     */
    public function up(): void
    {
        Schema::table('contas_financeiras', function (Blueprint $table) {
            $table->string('bandeira', 30)->nullable()->after('tipo')
                ->comment('Bandeira do cartão: VISA, MASTERCARD, ELO, AMEX, HIPERCARD');
            $table->unsignedTinyInteger('melhor_dia_compra')->nullable()->after('bandeira')
                ->comment('Melhor dia para compras (compras neste dia fecham na próxima fatura)');
            $table->unsignedTinyInteger('dia_fechamento_fatura')->nullable()->after('melhor_dia_compra')
                ->comment('Dia do fechamento da fatura (1-31)');
            $table->unsignedTinyInteger('dia_vencimento_fatura')->nullable()->after('dia_fechamento_fatura')
                ->comment('Dia do vencimento da fatura (1-31)');
        });
    }

    public function down(): void
    {
        Schema::table('contas_financeiras', function (Blueprint $table) {
            $table->dropColumn(['bandeira', 'melhor_dia_compra', 'dia_fechamento_fatura', 'dia_vencimento_fatura']);
        });
    }
};
