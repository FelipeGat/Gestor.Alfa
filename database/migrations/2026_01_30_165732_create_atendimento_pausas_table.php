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
        Schema::create('atendimento_pausas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atendimento_id')->constrained('atendimentos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_pausa', ['almoco', 'deslocamento', 'material', 'fim_dia']);
            $table->timestamp('iniciada_em');
            $table->timestamp('encerrada_em')->nullable();
            $table->integer('tempo_segundos')->default(0);
            $table->string('foto_inicio_path')->nullable();
            $table->string('foto_retorno_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atendimento_pausas');
    }
};
