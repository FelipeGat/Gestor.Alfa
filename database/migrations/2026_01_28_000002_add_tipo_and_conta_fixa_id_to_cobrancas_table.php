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
            $table->string('tipo')->default('orcamento')->after('status'); // orcamento, contrato, outros
            $table->foreignId('conta_fixa_id')->nullable()->after('orcamento_id')->constrained('contas_fixas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropForeign(['conta_fixa_id']);
            $table->dropColumn(['tipo', 'conta_fixa_id']);
        });
    }
};
