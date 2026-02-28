<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamento_setores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'nome']);
        });

        Schema::create('equipamento_responsaveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('nome');
            $table->string('cargo')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'nome']);
        });

        Schema::create('equipamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('setor_id')->nullable()->constrained('equipamento_setores')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('equipamento_responsaveis')->nullOnDelete();

            $table->string('nome');
            $table->string('modelo')->nullable();
            $table->string('fabricante')->nullable();
            $table->string('numero_serie')->nullable();

            $table->date('ultima_manutencao')->nullable();
            $table->date('ultima_limpeza')->nullable();

            $table->integer('periodicidade_manutencao_meses')->default(6);
            $table->integer('periodicidade_limpeza_meses')->default(1);

            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('qrcode_token')->unique();

            $table->timestamps();
        });

        Schema::create('equipamento_manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipamento_id')->constrained('equipamentos')->onDelete('cascade');

            $table->date('data');
            $table->enum('tipo', ['preventiva', 'correctiva', 'emergencial'])->default('preventiva');
            $table->text('descricao')->nullable();
            $table->string('realizado_por')->nullable();

            $table->timestamps();
        });

        Schema::create('equipamento_limpezas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipamento_id')->constrained('equipamentos')->onDelete('cascade');

            $table->date('data');
            $table->text('descricao')->nullable();
            $table->string('realizado_por')->nullable();

            $table->timestamps();
        });

        Schema::table('atendimentos', function (Blueprint $table) {
            $table->foreignId('equipamento_id')->nullable()->constrained('equipamentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropForeign(['equipamento_id']);
            $table->dropColumn('equipamento_id');
        });

        Schema::dropIfExists('equipamento_limpezas');
        Schema::dropIfExists('equipamento_manutencoes');
        Schema::dropIfExists('equipamentos');
        Schema::dropIfExists('equipamento_responsaveis');
        Schema::dropIfExists('equipamento_setores');
    }
};
