<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->unsignedBigInteger('setor_id')->nullable();
            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->string('nome');
            $table->string('modelo')->nullable();
            $table->string('fabricante')->nullable();
            $table->string('numero_serie')->nullable();
            $table->date('ultima_manutencao')->nullable();
            $table->date('ultima_limpeza')->nullable();
            $table->integer('periodicidade_manutencao_meses')->nullable();
            $table->integer('periodicidade_limpeza_meses')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('qrcode_token')->unique();
            $table->timestamps();
        });

        Schema::table('equipamentos', function (Blueprint $table) {
            $table->foreign('setor_id')->references('id')->on('equipamento_setores')->onDelete('set null');
            $table->foreign('responsavel_id')->references('id')->on('equipamento_responsaveis')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipamentos');
    }
};
