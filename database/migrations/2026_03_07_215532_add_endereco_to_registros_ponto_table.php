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
        Schema::table('registro_pontos_portal', function (Blueprint $table) {
            $table->string('endereco_logradouro', 255)->nullable()->after('saida_longitude');
            $table->string('endereco_numero', 20)->nullable()->after('endereco_logradouro');
            $table->string('endereco_bairro', 100)->nullable()->after('endereco_numero');
            $table->string('endereco_cidade', 100)->nullable()->after('endereco_bairro');
            $table->string('endereco_estado', 2)->nullable()->after('endereco_cidade');
            $table->string('endereco_cep', 10)->nullable()->after('endereco_estado');
            $table->text('endereco_formatado')->nullable()->after('endereco_cep');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_pontos_portal', function (Blueprint $table) {
            $table->dropColumn([
                'endereco_logradouro',
                'endereco_numero',
                'endereco_bairro',
                'endereco_cidade',
                'endereco_estado',
                'endereco_cep',
                'endereco_formatado',
            ]);
        });
    }
};
