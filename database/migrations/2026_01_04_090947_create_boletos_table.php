<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('boletos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('cliente_id')
              ->constrained()
              ->onDelete('cascade');

        $table->integer('mes');   // 1 a 12
        $table->integer('ano');   // 2025, 2026...

        $table->decimal('valor', 10, 2);

        $table->string('arquivo'); // caminho do PDF

        $table->enum('status', ['aberto', 'pago', 'vencido'])
              ->default('aberto');

        $table->timestamps();

        $table->unique(['cliente_id', 'mes', 'ano']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};