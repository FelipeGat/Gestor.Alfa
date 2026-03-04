<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql') {
            return false;
        }

        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$table, $constraint, 'FOREIGN KEY']
        );

        return (int) ($result->total ?? 0) > 0;
    }

    public function up(): void
    {
        if (Schema::hasTable('equipamento_setores') && ! Schema::hasColumn('equipamento_setores', 'cliente_id')) {
            Schema::table('equipamento_setores', function (Blueprint $table) {
                $table->foreignId('cliente_id')->nullable()->after('id')->constrained('clientes')->onDelete('set null');
            });
        }

        if (Schema::hasTable('equipamento_responsaveis') && ! Schema::hasColumn('equipamento_responsaveis', 'cliente_id')) {
            Schema::table('equipamento_responsaveis', function (Blueprint $table) {
                $table->foreignId('cliente_id')->nullable()->after('id')->constrained('clientes')->onDelete('set null');
            });
        }

        if (Schema::hasTable('equipamentos') && Schema::hasColumn('equipamentos', 'setor_id')) {
            $exists = $this->foreignKeyExists('equipamentos', 'equipamentos_setor_id_foreign');

            if (! $exists) {
                Schema::table('equipamentos', function (Blueprint $table) {
                    $table->foreign('setor_id')->references('id')->on('equipamento_setores')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('equipamentos') && Schema::hasColumn('equipamentos', 'responsavel_id')) {
            $exists = $this->foreignKeyExists('equipamentos', 'equipamentos_responsavel_id_foreign');

            if (! $exists) {
                Schema::table('equipamentos', function (Blueprint $table) {
                    $table->foreign('responsavel_id')->references('id')->on('equipamento_responsaveis')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
    }
};
