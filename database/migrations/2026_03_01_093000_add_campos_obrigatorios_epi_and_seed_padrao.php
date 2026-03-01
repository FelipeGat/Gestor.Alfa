<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('funcionario_epis')) {
            Schema::table('funcionario_epis', function (Blueprint $table) {
                if (!Schema::hasColumn('funcionario_epis', 'marca')) {
                    $table->string('marca', 120)->nullable()->after('data_entrega');
                }
                if (!Schema::hasColumn('funcionario_epis', 'quantidade')) {
                    $table->unsignedSmallInteger('quantidade')->nullable()->after('marca');
                }
                if (!Schema::hasColumn('funcionario_epis', 'tamanho')) {
                    $table->string('tamanho', 40)->nullable()->after('quantidade');
                }
                if (!Schema::hasColumn('funcionario_epis', 'numero_ca')) {
                    $table->string('numero_ca', 60)->nullable()->after('tamanho');
                }
                if (!Schema::hasColumn('funcionario_epis', 'data_vencimento')) {
                    $table->date('data_vencimento')->nullable()->after('numero_ca');
                }
            });

            DB::table('funcionario_epis')
                ->whereNull('data_vencimento')
                ->whereNotNull('data_prevista_troca')
                ->update(['data_vencimento' => DB::raw('data_prevista_troca')]);
        }

        if (Schema::hasTable('epis')) {
            $episPadrao = [
                'UNIFORME',
                'BOTA',
                'CAPACETE',
                'LUVA',
                'PROTETOR OURICULAR',
                'CINTO TALABARTE',
            ];

            foreach ($episPadrao as $nome) {
                $existe = DB::table('epis')
                    ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
                    ->exists();

                if (!$existe) {
                    DB::table('epis')->insert([
                        'nome' => $nome,
                        'ca' => 'N/I',
                        'validade_ca' => null,
                        'vida_util_meses' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('funcionario_epis')) {
            Schema::table('funcionario_epis', function (Blueprint $table) {
                if (Schema::hasColumn('funcionario_epis', 'data_vencimento')) {
                    $table->dropColumn('data_vencimento');
                }
                if (Schema::hasColumn('funcionario_epis', 'numero_ca')) {
                    $table->dropColumn('numero_ca');
                }
                if (Schema::hasColumn('funcionario_epis', 'tamanho')) {
                    $table->dropColumn('tamanho');
                }
                if (Schema::hasColumn('funcionario_epis', 'quantidade')) {
                    $table->dropColumn('quantidade');
                }
                if (Schema::hasColumn('funcionario_epis', 'marca')) {
                    $table->dropColumn('marca');
                }
            });
        }
    }
};
