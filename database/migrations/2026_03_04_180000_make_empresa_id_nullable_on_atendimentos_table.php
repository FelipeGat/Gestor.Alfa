<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE atendimentos MODIFY empresa_id BIGINT UNSIGNED NULL');

            return;
        }

        Schema::table('atendimentos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE atendimentos MODIFY empresa_id BIGINT UNSIGNED NOT NULL');

            return;
        }

        Schema::table('atendimentos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
        });
    }
};
