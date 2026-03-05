<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipamento_setores', function (Blueprint $table) {
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade')->after('id');
            $table->unique(['cliente_id', 'nome']);
        });

        Schema::table('equipamento_responsaveis', function (Blueprint $table) {
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade')->after('id');
            $table->unique(['cliente_id', 'nome']);
        });
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
