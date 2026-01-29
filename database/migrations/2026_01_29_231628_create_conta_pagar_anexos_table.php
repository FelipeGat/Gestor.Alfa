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
        Schema::create('conta_pagar_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_pagar_id')->constrained('contas_pagar')->onDelete('cascade');
            $table->enum('tipo', ['nf', 'boleto'])->comment('nf = Nota Fiscal, boleto = Boleto');
            $table->string('nome_original');
            $table->string('nome_arquivo')->unique();
            $table->string('caminho');
            $table->unsignedBigInteger('tamanho')->comment('Tamanho em bytes');
            $table->timestamps();

            $table->index('conta_pagar_id');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_pagar_anexos');
    }
};
