<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            $table->integer('numero');          // 185
            $table->string('tipo');             // NFS-e, NF-e, NFC-e
            $table->string('arquivo');          // caminho relativo
            $table->timestamp('baixado_em')->nullable();

            $table->timestamps();

            // Evita duplicação da mesma NF
            $table->unique(['cliente_id', 'numero', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};