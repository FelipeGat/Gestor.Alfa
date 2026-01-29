<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_pessoa', ['PF', 'PJ']);
            $table->string('cpf_cnpj')->unique();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('complemento')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fornecedor_contatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
            $table->string('nome');
            $table->string('cargo')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });

        // Adicionar fornecedor_id nas tabelas de contas a pagar
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->foreignId('fornecedor_id')->nullable()->after('centro_custo_id')->constrained('fornecedores')->onDelete('set null');
            if (!Schema::hasColumn('contas_pagar', 'forma_pagamento')) {
                $table->enum('forma_pagamento', ['PIX', 'BOLETO', 'TRANSFERENCIA', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'DINHEIRO', 'CHEQUE'])->nullable()->after('status');
            }
            $table->date('data_inicial')->nullable()->after('data_vencimento');
            $table->date('data_fim')->nullable()->after('data_inicial');
            $table->enum('periodicidade', ['DIARIA', 'SEMANAL', 'QUINZENAL', 'MENSAL', 'SEMESTRAL', 'ANUAL'])->nullable()->after('data_fim');
        });

        Schema::table('contas_fixas_pagar', function (Blueprint $table) {
            $table->foreignId('fornecedor_id')->nullable()->after('centro_custo_id')->constrained('fornecedores')->onDelete('set null');
            $table->enum('forma_pagamento', ['PIX', 'BOLETO', 'TRANSFERENCIA', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'DINHEIRO', 'CHEQUE'])->nullable()->after('valor');
            $table->enum('periodicidade', ['DIARIA', 'SEMANAL', 'QUINZENAL', 'MENSAL', 'SEMESTRAL', 'ANUAL'])->default('MENSAL')->after('forma_pagamento');
            $table->date('data_inicial')->after('dia_vencimento');
            $table->date('data_fim')->nullable()->after('data_inicial');
        });
    }

    public function down(): void
    {
        Schema::table('contas_fixas_pagar', function (Blueprint $table) {
            $table->dropForeign(['fornecedor_id']);
            $table->dropColumn(['fornecedor_id', 'forma_pagamento', 'periodicidade', 'data_inicial', 'data_fim']);
        });

        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropForeign(['fornecedor_id']);
            $table->dropColumn(['fornecedor_id', 'forma_pagamento', 'data_inicial', 'data_fim', 'periodicidade']);
        });

        Schema::dropIfExists('fornecedor_contatos');
        Schema::dropIfExists('fornecedores');
    }
};
