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
        Schema::create('contas_fixas_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_custo_id')->constrained('centros_custo')->cascadeOnDelete();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->integer('dia_vencimento');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_fixas_pagar');
    }
};
