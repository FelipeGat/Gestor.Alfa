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
                // Em bases novas a coluna em_pausa pode não existir.
                if (Schema::hasColumn('atendimentos', 'em_pausa')) {
                    $table->string('assinatura_cliente_path')->nullable()->after('em_pausa');
                } else {
                    $table->string('assinatura_cliente_path')->nullable();
                }
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
