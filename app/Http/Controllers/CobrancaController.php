<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cobranca;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CobrancaController extends Controller
{

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->canPermissao('cobrancas', 'ler'),
            403
        );

        // 🔎 Filtros
        $clienteId = $request->cliente_id;
        $mes       = $request->mes;
        $ano       = $request->ano;
        $status    = $request->status;

        $query = Cobranca::with(['cliente', 'boleto']);

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        if ($mes) {
            $query->whereMonth('data_vencimento', $mes);
        }

        if ($ano) {
            $query->whereYear('data_vencimento', $ano);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $cobrancas = $query
            ->orderBy('cliente_id')
            ->orderBy('data_vencimento')
            ->get()
            ->groupBy('cliente.nome');

        // 💰 Resumo financeiro
        $resumo = [
            'total_pago'     => 0,
            'total_pendente' => 0,
            'total_vencido'  => 0,
            'total_geral'    => 0,
        ];

        foreach ($query->get() as $cobranca) {
            $resumo['total_geral'] += $cobranca->valor;

            if ($cobranca->status === 'pago') {
                $resumo['total_pago'] += $cobranca->valor;
            } elseif ($cobranca->data_vencimento < now()) {
                $resumo['total_vencido'] += $cobranca->valor;
            } else {
                $resumo['total_pendente'] += $cobranca->valor;
            }
        }

        $clientes = Cliente::orderBy('nome')->get();

        return view('cobrancas.index', compact(
            'cobrancas',
            'clientes',
            'resumo'
        ));
    }

    public function marcarComoPago(Cobranca $cobranca)
    {
        if ($cobranca->status === 'pago') {
            return back()->with('info', 'Cobrança já está paga.');
        }

        $cobranca->update([
            'status'  => 'pago',
            'pago_em' => now(),
        ]);

        if ($cobranca->boleto) {
            $cobranca->boleto->update([
                'status' => 'pago',
            ]);
        }

        // Atualizar status do orçamento para 'concluido' quando todas as cobranças estiverem pagas
        if ($cobranca->orcamento_id) {
            $orcamento = $cobranca->orcamento;

            // Verifica se todas as cobranças do orçamento estão pagas
            $todasPagas = $orcamento->cobrancas()->where('status', '!=', 'pago')->count() === 0;

            if ($todasPagas && $orcamento->status === 'aguardando_pagamento') {
                $orcamento->update(['status' => 'concluido']);
            }
        }

        return back()->with('success', 'Cobrança marcada como paga.');
    }

    public function destroy(Cobranca $cobranca)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->canPermissao('cobrancas', 'excluir'),
            403
        );

        // Exclui boleto vinculado (se existir)
        if ($cobranca->boleto) {
            $cobranca->boleto->delete();
        }

        // Exclui a cobrança
        $cobranca->delete();

        return back()->with('success', 'Cobrança e boleto excluídos com sucesso.');
    }
}
