<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_comerciais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id')->nullable(); // NULL = meta global da empresa
            $table->tinyInteger('mes');  // 1–12
            $table->smallInteger('ano');
            $table->decimal('valor_meta', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['empresa_id', 'user_id', 'mes', 'ano'], 'unique_meta_comercial');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_comerciais');
    }
};
