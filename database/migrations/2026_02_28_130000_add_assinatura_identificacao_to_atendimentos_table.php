<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            if (!Schema::hasColumn('atendimentos', 'assinatura_cliente_nome')) {
                $table->string('assinatura_cliente_nome', 120)->nullable()->after('assinatura_cliente_path');
            }

            if (!Schema::hasColumn('atendimentos', 'assinatura_cliente_cargo')) {
                $table->string('assinatura_cliente_cargo', 120)->nullable()->after('assinatura_cliente_nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            if (Schema::hasColumn('atendimentos', 'assinatura_cliente_cargo')) {
                $table->dropColumn('assinatura_cliente_cargo');
            }

            if (Schema::hasColumn('atendimentos', 'assinatura_cliente_nome')) {
                $table->dropColumn('assinatura_cliente_nome');
            }
        });
    }
};
