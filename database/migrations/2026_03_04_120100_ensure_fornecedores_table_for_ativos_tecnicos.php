<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fornecedores')) {
            Schema::create('fornecedores', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('contato')->nullable();
                $table->string('telefone')->nullable();
                $table->string('email')->nullable();
                $table->text('observacoes')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('fornecedores', function (Blueprint $table) {
            if (! Schema::hasColumn('fornecedores', 'nome')) {
                $table->string('nome')->nullable()->after('id');
            }

            if (! Schema::hasColumn('fornecedores', 'contato')) {
                $table->string('contato')->nullable();
            }

            if (! Schema::hasColumn('fornecedores', 'telefone')) {
                $table->string('telefone')->nullable();
            }

            if (! Schema::hasColumn('fornecedores', 'email')) {
                $table->string('email')->nullable();
            }

            if (! Schema::hasColumn('fornecedores', 'observacoes')) {
                $table->text('observacoes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('fornecedores')) {
            return;
        }

        $columns = ['contato', 'telefone', 'email'];
        $existingColumns = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('fornecedores', $column)));

        if (! empty($existingColumns)) {
            Schema::table('fornecedores', function (Blueprint $table) use ($existingColumns) {
                $table->dropColumn($existingColumns);
            });
        }
    }
};
