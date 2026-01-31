<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('nota_fiscal')->default(false)->after('tipo_cliente');
        });
    }
    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('nota_fiscal');
        });
    }
};
