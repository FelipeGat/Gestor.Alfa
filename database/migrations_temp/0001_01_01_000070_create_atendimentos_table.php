<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nome');
            $table->string('tipo')->nullable();
            $table->string('categoria')->nullable();
            $table->string('subcategoria')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_atendimento')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('nome_solicitante')->nullable();
            $table->string('telefone_solicitante')->nullable();
            $table->string('email_solicitante')->nullable();
            $table->foreignId('assunto_id')->nullable()->constrained('assuntos')->onDelete('set null');
            $table->text('descricao');
            $table->enum('prioridade', ['BAIXA', 'MEDIA', 'ALTA', 'URGENTE'])->default('MEDIA');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->onDelete('set null');
            $table->string('status_atual')->default('PENDENTE');
            $table->boolean('is_orcamento')->default(false);
            $table->foreignId('atendimento_origem_id')->nullable()->constrained('atendimentos')->onDelete('set null');
            $table->timestamp('data_atendimento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('atendimento_status_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atendimento_id')->constrained('atendimentos')->onDelete('cascade');
            $table->string('status');
            $table->text('observacao')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('atendimento_andamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atendimento_id')->constrained('atendimentos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('descricao');
            $table->timestamps();
        });

        Schema::create('atendimento_andamento_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atendimento_andamento_id')->constrained('atendimento_andamentos')->onDelete('cascade');
            $table->string('arquivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimento_andamento_fotos');
        Schema::dropIfExists('atendimento_andamentos');
        Schema::dropIfExists('atendimento_status_historicos');
        Schema::dropIfExists('atendimentos');
        Schema::dropIfExists('assuntos');
    }
};
