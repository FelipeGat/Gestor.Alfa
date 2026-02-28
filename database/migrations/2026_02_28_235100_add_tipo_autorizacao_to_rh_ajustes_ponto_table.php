<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rh_ajustes_ponto', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_ajustes_ponto', 'tipo_ajuste')) {
                $table->string('tipo_ajuste')->default('compensacao')->after('minutos_ajuste');
            }

            if (!Schema::hasColumn('rh_ajustes_ponto', 'autorizado_por_user_id')) {
                $table->foreignId('autorizado_por_user_id')->nullable()->after('ajustado_por_user_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rh_ajustes_ponto', function (Blueprint $table) {
            if (Schema::hasColumn('rh_ajustes_ponto', 'autorizado_por_user_id')) {
                $table->dropConstrainedForeignId('autorizado_por_user_id');
            }

            if (Schema::hasColumn('rh_ajustes_ponto', 'tipo_ajuste')) {
                $table->dropColumn('tipo_ajuste');
            }
        });
    }
};
