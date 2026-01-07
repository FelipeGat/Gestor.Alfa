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
        Schema::create('notas_fiscais', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();

    $table->integer('numero'); // 185
    $table->string('tipo')->default('NFS-e');
    $table->string('arquivo'); // caminho do PDF

    $table->timestamp('baixado_em')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};