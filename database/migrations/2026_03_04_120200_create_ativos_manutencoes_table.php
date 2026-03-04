<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ativos_manutencoes')) {
            return;
        }

        Schema::create('ativos_manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ativo_id')->constrained('equipamentos')->cascadeOnDelete();
            $table->date('data_manutencao');
            $table->enum('tipo', ['preventiva', 'corretiva', 'limpeza'])->default('preventiva');
            $table->text('descricao')->nullable();
            $table->string('tecnico_responsavel', 150)->nullable();
            $table->decimal('custo', 10, 2)->nullable();
            $table->text('pecas_trocadas')->nullable();
            $table->integer('tempo_parado_horas')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ativos_manutencoes');
    }
};
