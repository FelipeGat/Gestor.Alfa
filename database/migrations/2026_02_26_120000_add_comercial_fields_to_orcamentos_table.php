<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dateTime('data_envio')->nullable()->after('status');
            $table->dateTime('data_aprovacao')->nullable()->after('data_envio');
            $table->foreignId('vendedor_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('origem_lead')->nullable()->after('pre_cliente_id');
            $table->decimal('probabilidade_fechamento', 5, 2)->default(0)->after('origem_lead');

            $table->index(['empresa_id', 'status', 'created_at'], 'orcamentos_empresa_status_created_idx');
            $table->index(['vendedor_id', 'status', 'created_at'], 'orcamentos_vendedor_status_created_idx');
            $table->index('data_envio', 'orcamentos_data_envio_idx');
            $table->index('data_aprovacao', 'orcamentos_data_aprovacao_idx');
            $table->index('origem_lead', 'orcamentos_origem_lead_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropIndex('orcamentos_origem_lead_idx');
            $table->dropIndex('orcamentos_data_aprovacao_idx');
            $table->dropIndex('orcamentos_data_envio_idx');
            $table->dropIndex('orcamentos_vendedor_status_created_idx');
            $table->dropIndex('orcamentos_empresa_status_created_idx');

            $table->dropForeign(['vendedor_id']);
            $table->dropColumn([
                'data_envio',
                'data_aprovacao',
                'vendedor_id',
                'origem_lead',
                'probabilidade_fechamento',
            ]);
        });
    }
};
