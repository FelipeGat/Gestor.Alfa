<?php

namespace App\Services;

use App\Models\MovimentacaoFinanceira;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MovimentacaoFinanceiraService
{
    /**
     * Cria uma movimentação financeira (ajuste, transferência, injeção)
     */
    public function registrar(array $dados): MovimentacaoFinanceira
    {
        return DB::transaction(function () use ($dados) {
            $mov = new MovimentacaoFinanceira();
            $mov->conta_origem_id = $dados['conta_origem_id'] ?? null;
            $mov->conta_destino_id = $dados['conta_destino_id'] ?? null;
            $mov->tipo = $dados['tipo'];
            $mov->valor = $dados['valor'];
            $mov->observacao = $dados['observacao'] ?? null;
            $mov->user_id = $dados['user_id'] ?? Auth::id();
            $mov->data_movimentacao = $dados['data_movimentacao'] ?? Carbon::now();
            $mov->save();

            // Reprocessa saldo da conta origem, se houver
            if ($mov->conta_origem_id) {
                $contaOrigem = \App\Models\ContaFinanceira::find($mov->conta_origem_id);
                if ($contaOrigem) {
                    $contaOrigem->reprocessarSaldo($mov->data_movimentacao);
                }
            }
            // Reprocessa saldo da conta destino, se houver
            if ($mov->conta_destino_id) {
                $contaDestino = \App\Models\ContaFinanceira::find($mov->conta_destino_id);
                if ($contaDestino) {
                    $contaDestino->reprocessarSaldo($mov->data_movimentacao);
                }
            }
            return $mov;
        });
    }
}
