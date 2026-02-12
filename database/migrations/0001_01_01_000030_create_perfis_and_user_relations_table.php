<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfis', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Add user_type to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('tipo')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->onDelete('set null');
            $table->boolean('primeiro_acesso')->default(true);
        });

        // Tabela pivot perfil_user
        Schema::create('perfil_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_id')->constrained('perfis')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Tabela pivot user_empresa
        Schema::create('user_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->timestamps();
        });

        // Tabela pivot cliente_user (multi-unidade)
        Schema::create('cliente_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_user');
        Schema::dropIfExists('user_empresa');
        Schema::dropIfExists('perfil_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['funcionario_id']);
            $table->dropColumn(['tipo', 'cliente_id', 'funcionario_id', 'primeiro_acesso']);
        });
        Schema::dropIfExists('perfis');
    }
};
