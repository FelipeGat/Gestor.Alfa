<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            if (!Schema::hasColumn('atendimentos', 'assinatura_cliente_path')) {
                $table->string('assinatura_cliente_path')->nullable()->after('em_pausa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            if (Schema::hasColumn('atendimentos', 'assinatura_cliente_path')) {
                $table->dropColumn('assinatura_cliente_path');
            }
        });
    }
};
