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
        Schema::create('atendimento_andamento_fotos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('atendimento_andamento_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('arquivo');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atendimento_andamento_fotos');
    }
};