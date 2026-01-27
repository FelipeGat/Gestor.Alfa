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
            if (Schema::hasColumn('cobrancas', 'pre_cliente_id')) {
                $table->dropColumn('pre_cliente_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (!Schema::hasColumn('cobrancas', 'pre_cliente_id')) {
                $table->unsignedBigInteger('pre_cliente_id')->nullable()->after('cliente_id');
            }
        });
    }
};
