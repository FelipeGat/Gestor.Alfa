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
            // Permitir referência a pré-cliente quando aplicável

            // Forma de pagamento (pix, debito, credito, boleto, faturado)
            if (!Schema::hasColumn('cobrancas', 'forma_pagamento')) {
                $table->string('forma_pagamento', 50)->nullable()->after('origem');
            }

            // Parcelamento: índice da parcela atual e total de parcelas
            if (!Schema::hasColumn('cobrancas', 'parcela_num')) {
                $table->unsignedInteger('parcela_num')->nullable()->after('forma_pagamento');
            }

            if (!Schema::hasColumn('cobrancas', 'parcelas_total')) {
                $table->unsignedInteger('parcelas_total')->nullable()->after('parcela_num');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (Schema::hasColumn('cobrancas', 'parcelas_total')) {
                $table->dropColumn('parcelas_total');
            }

            if (Schema::hasColumn('cobrancas', 'parcela_num')) {
                $table->dropColumn('parcela_num');
            }

            if (Schema::hasColumn('cobrancas', 'forma_pagamento')) {
                $table->dropColumn('forma_pagamento');
            }
        });
    }
};
