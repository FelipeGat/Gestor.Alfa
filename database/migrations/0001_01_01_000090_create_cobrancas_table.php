<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->integer('mes');
            $table->integer('ano');
            $table->decimal('valor', 10, 2);
            $table->string('arquivo')->nullable();
            $table->string('status')->default('PENDENTE');
            $table->date('data_vencimento');
            $table->timestamp('baixado_em')->nullable();
            $table->timestamps();
        });

        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('numero');
            $table->string('tipo'); // entrada, saida
            $table->string('arquivo')->nullable();
            $table->timestamp('baixado_em')->nullable();
            $table->timestamps();
        });

        Schema::create('contas_fixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('categoria');
            $table->decimal('valor', 10, 2);
            $table->foreignId('conta_financeira_id')->nullable()->constrained('contas_financeiras')->onDelete('set null');
            $table->string('forma_pagamento')->nullable();
            $table->string('periodicidade')->default('MENSAL');
            $table->date('data_inicial');
            $table->date('data_fim')->nullable();
            $table->decimal('percentual_renovacao', 5, 2)->nullable();
            $table->date('data_atualizacao_percentual')->nullable();
            $table->text('observacao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('cobrancas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->nullable()->constrained('orcamentos')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('conta_financeira_id')->nullable()->constrained('contas_financeiras')->onDelete('set null');
            $table->foreignId('boleto_id')->nullable()->constrained('boletos')->onDelete('set null');
            $table->foreignId('conta_fixa_id')->nullable()->constrained('contas_fixas')->onDelete('set null');
            $table->text('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->string('status')->default('PENDENTE');
            $table->string('tipo')->nullable(); // avulsa, recorrente
            $table->timestamp('pago_em')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->integer('parcela_num')->nullable();
            $table->integer('parcelas_total')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
        Schema::dropIfExists('contas_fixas');
        Schema::dropIfExists('notas_fiscais');
        Schema::dropIfExists('boletos');
    }
};
