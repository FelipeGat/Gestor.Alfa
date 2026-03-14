<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimento_tecnicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atendimento_id')
                  ->constrained('atendimentos')
                  ->onDelete('cascade');
            $table->foreignId('funcionario_id')
                  ->constrained('funcionarios')
                  ->onDelete('cascade');
            $table->timestamps();

            // Um técnico não pode aparecer duas vezes no mesmo atendimento
            $table->unique(['atendimento_id', 'funcionario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimento_tecnicos');
    }
};
