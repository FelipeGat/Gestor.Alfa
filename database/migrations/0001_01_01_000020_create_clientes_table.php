<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->boolean('ativo')->default(true);
            $table->decimal('valor_mensal', 10, 2)->nullable();
            $table->integer('dia_vencimento')->nullable();
            $table->enum('tipo_pessoa', ['FISICA', 'JURIDICA'])->nullable();
            $table->string('cpf_cnpj')->nullable();
            $table->string('razao_social')->nullable();
            $table->enum('tipo_cliente', ['NORMAL', 'VIP', 'PROSPECT'])->nullable();
            $table->date('data_cadastro')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('complemento')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('inscricao_municipal')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela pivot cliente_empresa
        Schema::create('cliente_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_empresa');
        Schema::dropIfExists('clientes');
    }
};
