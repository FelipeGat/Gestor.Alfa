<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamento_manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipamento_id')->constrained()->onDelete('cascade');
            $table->date('data');
            $table->enum('tipo', ['preventiva', 'corretiva', 'preditiva'])->default('preventiva');
            $table->text('descricao')->nullable();
            $table->string('realizado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipamento_manutencoes');
    }
};
