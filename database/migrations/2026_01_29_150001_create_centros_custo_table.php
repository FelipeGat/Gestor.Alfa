<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('centros_custo', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['GRUPO', 'CNPJ'])->default('GRUPO');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Foreign key condicional - só adiciona se a tabela empresas existir
            if (Schema::hasTable('empresas')) {
                $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_custo');
    }
};
