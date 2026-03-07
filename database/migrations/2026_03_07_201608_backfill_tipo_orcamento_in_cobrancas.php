<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige registros existentes na tabela cobrancas onde tipo era NULL
     * para cobranças originadas de orçamentos.
     *
     * Causa raiz: FinanceiroController::gerarCobranca() não definia 'tipo' => 'orcamento'
     * ao criar Cobranca, deixando o campo NULL. Cobranças de contratos (ContaFixa)
     * já eram gravadas corretamente com tipo='contrato'.
     */
    public function up(): void
    {
        // Corrige: orcamento_id preenchido + tipo NULL → tipo = 'orcamento'
        DB::table('cobrancas')
            ->whereNotNull('orcamento_id')
            ->whereNull('tipo')
            ->update(['tipo' => 'orcamento']);
    }

    public function down(): void
    {
        // Reverte apenas registros que tinham NULL antes (não toca em novos registros
        // gravados corretamente pelo código já corrigido, que têm conta_fixa_id NULL)
        DB::table('cobrancas')
            ->whereNotNull('orcamento_id')
            ->where('tipo', 'orcamento')
            ->whereNull('conta_fixa_id')
            ->update(['tipo' => null]);
    }
};
