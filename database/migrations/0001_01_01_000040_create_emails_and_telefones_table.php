<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('valor');
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });

        Schema::create('telefones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('valor');
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });

        Schema::create('cliente_contatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('tipo'); // email, telefone, celular
            $table->string('valor');
            $table->boolean('principal')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_contatos');
        Schema::dropIfExists('telefones');
        Schema::dropIfExists('emails');
    }
};
