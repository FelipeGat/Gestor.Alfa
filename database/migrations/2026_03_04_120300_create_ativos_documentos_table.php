<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ativos_documentos')) {
            return;
        }

        Schema::create('ativos_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ativo_id')->constrained('equipamentos')->cascadeOnDelete();
            $table->string('nome_documento');
            $table->string('arquivo');
            $table->string('tipo_documento', 50);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ativos_documentos');
    }
};
