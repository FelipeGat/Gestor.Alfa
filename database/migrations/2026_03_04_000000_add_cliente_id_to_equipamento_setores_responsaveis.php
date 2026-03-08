<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('equipamento_setores', 'cliente_id')) {
            Schema::table('equipamento_setores', function (Blueprint $table) {
                $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade')->after('id');
                $table->unique(['cliente_id', 'nome']);
            });
        }

        if (!Schema::hasColumn('equipamento_responsaveis', 'cliente_id')) {
            Schema::table('equipamento_responsaveis', function (Blueprint $table) {
                $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade')->after('id');
                $table->unique(['cliente_id', 'nome']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('equipamento_setores', function (Blueprint $table) {
            $table->dropUnique(['cliente_id', 'nome']);
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });

        Schema::table('equipamento_responsaveis', function (Blueprint $table) {
            $table->dropUnique(['cliente_id', 'nome']);
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};
