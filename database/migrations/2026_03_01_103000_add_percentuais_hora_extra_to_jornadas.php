<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jornadas')) {
            Schema::table('jornadas', function (Blueprint $table) {
                if (!Schema::hasColumn('jornadas', 'percentual_hora_extra_semana')) {
                    $table->decimal('percentual_hora_extra_semana', 5, 2)->default(50)->after('minimo_horas_para_extra');
                }

                if (!Schema::hasColumn('jornadas', 'percentual_hora_extra_domingo_feriado')) {
                    $table->decimal('percentual_hora_extra_domingo_feriado', 5, 2)->default(100)->after('percentual_hora_extra_semana');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jornadas')) {
            Schema::table('jornadas', function (Blueprint $table) {
                if (Schema::hasColumn('jornadas', 'percentual_hora_extra_domingo_feriado')) {
                    $table->dropColumn('percentual_hora_extra_domingo_feriado');
                }

                if (Schema::hasColumn('jornadas', 'percentual_hora_extra_semana')) {
                    $table->dropColumn('percentual_hora_extra_semana');
                }
            });
        }
    }
};
