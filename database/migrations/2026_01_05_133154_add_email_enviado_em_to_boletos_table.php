<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('boletos', function (Blueprint $table) {
        $table->timestamp('email_enviado_em')->nullable()->after('baixado_em');
    });
}

public function down()
{
    Schema::table('boletos', function (Blueprint $table) {
        $table->dropColumn('email_enviado_em');
    });
}
};