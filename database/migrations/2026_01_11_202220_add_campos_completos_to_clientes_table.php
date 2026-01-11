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
        Schema::table('clientes', function (Blueprint $table) {

             $table->date('data_cadastro')->nullable()->after('tipo_cliente');

            $table->string('cep')->nullable()->after('data_cadastro');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('numero')->nullable()->after('logradouro');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('cidade')->nullable()->after('complemento');

            $table->text('observacoes')->nullable()->after('cidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {

            $table->dropUnique(['cpf_cnpj']);

            $table->dropColumn([
                'data_cadastro',
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'cidade',
                'observacoes',
            ]);
        });
    }
};