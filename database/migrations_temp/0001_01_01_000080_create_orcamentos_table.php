<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_comerciais', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['PRODUTO', 'SERVICO']);
            $table->string('nome');
            $table->string('sku_ou_referencia')->nullable();
            $table->string('codigo_barras_ean')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('assuntos')->onDelete('set null');
            $table->decimal('preco_venda', 10, 2)->nullable();
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('margem_lucro', 5, 2)->nullable();
            $table->string('unidade_medida')->nullable();
            $table->integer('estoque_atual')->default(0);
            $table->integer('estoque_minimo')->nullable();
            $table->boolean('gerencia_estoque')->default(false);
            $table->string('finalidade')->nullable();
            $table->string('ncm')->nullable();
            $table->string('cfop_padrao')->nullable();
            $table->string('codigo_servico_iss')->nullable();
            $table->decimal('aliquota_icms', 5, 2)->nullable();
            $table->decimal('aliquota_iss', 5, 2)->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('estado')->nullable();
            $table->decimal('custo_frete', 10, 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('set null');
            $table->foreignId('atendimento_id')->nullable()->constrained('atendimentos')->onDelete('set null');
            $table->text('descricao')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('pre_cliente_id')->nullable()->constrained('pre_clientes')->onDelete('set null');
            $table->string('numero_orcamento')->unique();
            $table->string('status')->default('PENDENTE');
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->nullable();
            $table->decimal('desconto_servico_valor', 10, 2)->nullable();
            $table->string('desconto_servico_tipo')->nullable();
            $table->decimal('desconto_produto_valor', 10, 2)->nullable();
            $table->string('desconto_produto_tipo')->nullable();
            $table->decimal('taxas', 10, 2)->nullable();
            $table->text('descricao_taxas')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->string('prazo_pagamento')->nullable();
            $table->date('validade')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('orcamento_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');
            $table->foreignId('item_comercial_id')->nullable()->constrained('itens_comerciais')->onDelete('set null');
            $table->enum('tipo', ['PRODUTO', 'SERVICO']);
            $table->string('nome');
            $table->integer('quantidade')->default(1);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('orcamento_taxas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');
            $table->string('nome');
            $table->enum('tipo', ['FIXA', 'PERCENTUAL']);
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });

        Schema::create('orcamento_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');
            $table->string('tipo'); // avista, parcelado, etc
            $table->integer('parcelas')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_pagamentos');
        Schema::dropIfExists('orcamento_taxas');
        Schema::dropIfExists('orcamento_itens');
        Schema::dropIfExists('orcamentos');
        Schema::dropIfExists('itens_comerciais');
    }
};
