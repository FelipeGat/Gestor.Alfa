<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->adicionarIndice('cobrancas', 'idx_cobrancas_status', ['status']);
        $this->adicionarIndice('cobrancas', 'idx_cobrancas_data_vencimento', ['data_vencimento']);
        $this->adicionarIndice('cobrancas', 'idx_cobrancas_data_pagamento', ['data_pagamento']);

        $this->adicionarIndice('contas_pagar', 'idx_contas_pagar_status', ['status']);
        $this->adicionarIndice('contas_pagar', 'idx_contas_pagar_data_vencimento', ['data_vencimento']);
        $this->adicionarIndice('contas_pagar', 'idx_contas_pagar_data_pagamento', ['data_pagamento']);

        $this->adicionarIndice('atendimentos', 'idx_atendimentos_status_atual', ['status_atual']);
        $this->adicionarIndice('atendimentos', 'idx_atendimentos_data_atendimento', ['data_atendimento']);

        $this->adicionarIndice('movimentacoes_financeiras', 'idx_mov_fin_empresa_id', ['empresa_id']);
        $this->adicionarIndice('movimentacoes_financeiras', 'idx_mov_fin_status', ['status']);
        $this->adicionarIndice('movimentacoes_financeiras', 'idx_mov_fin_data_movimentacao', ['data_movimentacao']);
    }

    public function down(): void
    {
        $this->removerIndice('cobrancas', 'idx_cobrancas_status');
        $this->removerIndice('cobrancas', 'idx_cobrancas_data_vencimento');
        $this->removerIndice('cobrancas', 'idx_cobrancas_data_pagamento');

        $this->removerIndice('contas_pagar', 'idx_contas_pagar_status');
        $this->removerIndice('contas_pagar', 'idx_contas_pagar_data_vencimento');
        $this->removerIndice('contas_pagar', 'idx_contas_pagar_data_pagamento');

        $this->removerIndice('atendimentos', 'idx_atendimentos_status_atual');
        $this->removerIndice('atendimentos', 'idx_atendimentos_data_atendimento');

        $this->removerIndice('movimentacoes_financeiras', 'idx_mov_fin_empresa_id');
        $this->removerIndice('movimentacoes_financeiras', 'idx_mov_fin_status');
        $this->removerIndice('movimentacoes_financeiras', 'idx_mov_fin_data_movimentacao');
    }

    private function adicionarIndice(string $tabela, string $nomeIndice, array $colunas): void
    {
        if (! Schema::hasTable($tabela)) {
            return;
        }

        foreach ($colunas as $coluna) {
            if (! Schema::hasColumn($tabela, $coluna)) {
                return;
            }
        }

        if ($this->indiceExiste($tabela, $nomeIndice)) {
            return;
        }

        Schema::table($tabela, function (Blueprint $table) use ($colunas, $nomeIndice): void {
            $table->index($colunas, $nomeIndice);
        });
    }

    private function removerIndice(string $tabela, string $nomeIndice): void
    {
        if (! Schema::hasTable($tabela) || ! $this->indiceExiste($tabela, $nomeIndice)) {
            return;
        }

        Schema::table($tabela, function (Blueprint $table) use ($nomeIndice): void {
            $table->dropIndex($nomeIndice);
        });
    }

    private function indiceExiste(string $tabela, string $nomeIndice): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $tabelaEscapada = str_replace("'", "''", $tabela);
            $indices = DB::select("PRAGMA index_list('{$tabelaEscapada}')");

            foreach ($indices as $indice) {
                if (($indice->name ?? null) === $nomeIndice) {
                    return true;
                }
            }

            return false;
        }

        $resultado = DB::selectOne(
            'SELECT COUNT(*) as total FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$tabela, $nomeIndice]
        );

        return ((int) ($resultado->total ?? 0)) > 0;
    }
};
