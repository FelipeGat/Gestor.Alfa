<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('status')->constrained('users');
        });
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('status')->constrained('users');
        });
    }

    public function down()
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
