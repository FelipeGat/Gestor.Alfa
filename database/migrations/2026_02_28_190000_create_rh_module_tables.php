<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->unsignedSmallInteger('intervalo_minutos')->default(0);
            $table->decimal('carga_horaria_semanal', 5, 2);
            $table->timestamps();
        });

        Schema::create('funcionario_jornadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->foreignId('jornada_id')->constrained('jornadas')->cascadeOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->timestamps();

            $table->index(['funcionario_id', 'data_inicio'], 'idx_funcionario_jornadas_inicio');
        });

        Schema::create('epis', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('ca');
            $table->date('validade_ca')->nullable();
            $table->unsignedSmallInteger('vida_util_meses')->nullable();
            $table->timestamps();
        });

        Schema::create('funcionario_epis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->foreignId('epi_id')->constrained('epis')->cascadeOnDelete();
            $table->date('data_entrega');
            $table->date('data_prevista_troca')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();

            $table->index(['funcionario_id', 'data_prevista_troca'], 'idx_funcionario_epis_troca');
        });

        Schema::create('funcionario_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('numero')->nullable();
            $table->date('data_emissao')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->string('arquivo')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();

            $table->index(['funcionario_id', 'data_vencimento'], 'idx_funcionario_documentos_vencimento');
        });

        Schema::create('funcionario_beneficios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->enum('tipo', ['VT', 'VR', 'VA', 'Outro']);
            $table->decimal('valor', 12, 2)->default(0);
            $table->decimal('desconto_percentual', 5, 2)->default(0);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->timestamps();

            $table->index(['funcionario_id', 'data_inicio'], 'idx_funcionario_beneficios_inicio');
        });

        Schema::create('ferias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->date('periodo_aquisitivo_inicio');
            $table->date('periodo_aquisitivo_fim');
            $table->date('periodo_gozo_inicio')->nullable();
            $table->date('periodo_gozo_fim')->nullable();
            $table->string('status')->default('pendente');
            $table->timestamps();

            $table->index(['funcionario_id', 'periodo_gozo_fim'], 'idx_ferias_gozo_fim');
        });

        Schema::create('afastamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->string('tipo');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index(['funcionario_id', 'data_inicio'], 'idx_afastamentos_inicio');
        });

        Schema::create('advertencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->string('tipo');
            $table->text('descricao');
            $table->date('data');
            $table->timestamps();

            $table->index(['funcionario_id', 'data'], 'idx_advertencias_data');
        });

        Schema::create('rh_ajustes_ponto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->foreignId('atendimento_id')->nullable()->constrained('atendimentos')->nullOnDelete();
            $table->integer('minutos_ajuste');
            $table->text('justificativa');
            $table->foreignId('ajustado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ajustado_em');
            $table->timestamps();

            $table->index(['funcionario_id', 'ajustado_em'], 'idx_rh_ajustes_ponto_funcionario_data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_ajustes_ponto');
        Schema::dropIfExists('advertencias');
        Schema::dropIfExists('afastamentos');
        Schema::dropIfExists('ferias');
        Schema::dropIfExists('funcionario_beneficios');
        Schema::dropIfExists('funcionario_documentos');
        Schema::dropIfExists('funcionario_epis');
        Schema::dropIfExists('epis');
        Schema::dropIfExists('funcionario_jornadas');
        Schema::dropIfExists('jornadas');
    }
};
