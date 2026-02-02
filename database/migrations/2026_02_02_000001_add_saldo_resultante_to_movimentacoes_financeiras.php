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
        Schema::table('movimentacoes_financeiras', function (Blueprint $table) {
            $table->decimal('saldo_resultante', 15, 2)->nullable()->after('valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes_financeiras', function (Blueprint $table) {
            $table->dropColumn('saldo_resultante');
        });
    }
};
