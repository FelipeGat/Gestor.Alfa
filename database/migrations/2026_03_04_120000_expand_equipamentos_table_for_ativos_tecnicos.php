<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('equipamentos')) {
            return;
        }

        Schema::table('equipamentos', function (Blueprint $table) {
            if (! Schema::hasColumn('equipamentos', 'codigo_ativo')) {
                $table->string('codigo_ativo', 50)->nullable()->after('numero_serie');
            }

            if (! Schema::hasColumn('equipamentos', 'tag_patrimonial')) {
                $table->string('tag_patrimonial', 50)->nullable()->after('codigo_ativo');
            }

            if (! Schema::hasColumn('equipamentos', 'data_aquisicao')) {
                $table->date('data_aquisicao')->nullable()->after('tag_patrimonial');
            }

            if (! Schema::hasColumn('equipamentos', 'data_instalacao')) {
                $table->date('data_instalacao')->nullable()->after('data_aquisicao');
            }

            if (! Schema::hasColumn('equipamentos', 'vida_util_anos')) {
                $table->integer('vida_util_anos')->nullable()->after('data_instalacao');
            }

            if (! Schema::hasColumn('equipamentos', 'capacidade')) {
                $table->string('capacidade', 100)->nullable()->after('vida_util_anos');
            }

            if (! Schema::hasColumn('equipamentos', 'potencia')) {
                $table->string('potencia', 100)->nullable()->after('capacidade');
            }

            if (! Schema::hasColumn('equipamentos', 'voltagem')) {
                $table->string('voltagem', 50)->nullable()->after('potencia');
            }

            if (! Schema::hasColumn('equipamentos', 'status_ativo')) {
                $table->enum('status_ativo', [
                    'operando',
                    'em_manutencao',
                    'inativo',
                    'aguardando_peca',
                    'descartado',
                    'substituido',
                ])->nullable()->after('voltagem');
            }

            if (! Schema::hasColumn('equipamentos', 'criticidade')) {
                $table->enum('criticidade', ['baixa', 'media', 'alta', 'critica'])->nullable()->after('status_ativo');
            }

            if (! Schema::hasColumn('equipamentos', 'possui_garantia')) {
                $table->boolean('possui_garantia')->default(false)->after('criticidade');
            }

            if (! Schema::hasColumn('equipamentos', 'garantia_inicio')) {
                $table->date('garantia_inicio')->nullable()->after('possui_garantia');
            }

            if (! Schema::hasColumn('equipamentos', 'garantia_fim')) {
                $table->date('garantia_fim')->nullable()->after('garantia_inicio');
            }

            if (! Schema::hasColumn('equipamentos', 'valor_aquisicao')) {
                $table->decimal('valor_aquisicao', 12, 2)->nullable()->after('garantia_fim');
            }

            if (! Schema::hasColumn('equipamentos', 'fornecedor_id')) {
                $table->unsignedBigInteger('fornecedor_id')->nullable()->after('valor_aquisicao');
            }

            if (! Schema::hasColumn('equipamentos', 'unidade')) {
                $table->string('unidade', 150)->nullable()->after('fornecedor_id');
            }

            if (! Schema::hasColumn('equipamentos', 'andar')) {
                $table->string('andar', 50)->nullable()->after('unidade');
            }

            if (! Schema::hasColumn('equipamentos', 'sala')) {
                $table->string('sala', 50)->nullable()->after('andar');
            }

            if (! Schema::hasColumn('equipamentos', 'localizacao_detalhada')) {
                $table->text('localizacao_detalhada')->nullable()->after('sala');
            }

            if (! Schema::hasColumn('equipamentos', 'qr_code')) {
                $table->string('qr_code', 255)->nullable()->after('qrcode_token');
            }

            if (! Schema::hasColumn('equipamentos', 'foto_principal')) {
                $table->string('foto_principal', 255)->nullable()->after('qr_code');
            }
        });

        if (Schema::hasTable('fornecedores')
            && Schema::hasColumn('equipamentos', 'fornecedor_id')
            && ! $this->foreignKeyExists('equipamentos', 'equipamentos_fornecedor_id_foreign')) {
            Schema::table('equipamentos', function (Blueprint $table) {
                $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('equipamentos')) {
            return;
        }

        if ($this->foreignKeyExists('equipamentos', 'equipamentos_fornecedor_id_foreign')) {
            Schema::table('equipamentos', function (Blueprint $table) {
                $table->dropForeign('equipamentos_fornecedor_id_foreign');
            });
        }

        $columns = [
            'codigo_ativo',
            'tag_patrimonial',
            'data_aquisicao',
            'data_instalacao',
            'vida_util_anos',
            'capacidade',
            'potencia',
            'voltagem',
            'status_ativo',
            'criticidade',
            'possui_garantia',
            'garantia_inicio',
            'garantia_fim',
            'valor_aquisicao',
            'fornecedor_id',
            'unidade',
            'andar',
            'sala',
            'localizacao_detalhada',
            'qr_code',
            'foto_principal',
        ];

        $existingColumns = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('equipamentos', $column)));

        if (! empty($existingColumns)) {
            Schema::table('equipamentos', function (Blueprint $table) use ($existingColumns) {
                $table->dropColumn($existingColumns);
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return false;
        }

        $result = Schema::getConnection()->selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$table, $constraint, 'FOREIGN KEY']
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
