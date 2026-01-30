<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar campos na tabela cobrancas
        Schema::table('cobrancas', function (Blueprint $table) {
            if (!Schema::hasColumn('cobrancas', 'data_pagamento')) {
                $table->date('data_pagamento')->nullable()->after('pago_em');
            }
            if (!Schema::hasColumn('cobrancas', 'juros_multa')) {
                $table->decimal('juros_multa', 10, 2)->default(0)->after('valor');
            }
        });

        // Adicionar campos na tabela contas_pagar
        Schema::table('contas_pagar', function (Blueprint $table) {
            if (!Schema::hasColumn('contas_pagar', 'data_pagamento')) {
                $table->date('data_pagamento')->nullable()->after('pago_em');
            }
            if (!Schema::hasColumn('contas_pagar', 'juros_multa')) {
                $table->decimal('juros_multa', 10, 2)->default(0)->after('valor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (Schema::hasColumn('cobrancas', 'data_pagamento')) {
                $table->dropColumn('data_pagamento');
            }
            if (Schema::hasColumn('cobrancas', 'juros_multa')) {
                $table->dropColumn('juros_multa');
            }
        });

        Schema::table('contas_pagar', function (Blueprint $table) {
            if (Schema::hasColumn('contas_pagar', 'data_pagamento')) {
                $table->dropColumn('data_pagamento');
            }
            if (Schema::hasColumn('contas_pagar', 'juros_multa')) {
                $table->dropColumn('juros_multa');
            }
        });
    }
};
