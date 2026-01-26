<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContasReceberController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Sua lógica de segurança original foi mantida.
        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // ================= PREPARAÇÃO DA QUERY BASE =================
        $query = Cobranca::with('cliente:id,nome_fantasia')
            ->select('cobrancas.*')
            ->join('clientes', 'clientes.id', '=', 'cobrancas.cliente_id');

        // ================= APLICAÇÃO DOS FILTROS =================

        // Filtro de Busca (Cliente ou Descrição)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('clientes.nome_fantasia', 'like', $searchTerm)
                    ->orWhere('cobrancas.descricao', 'like', $searchTerm);
            });
        }

        // Filtro de Período por Vencimento
        if ($request->filled('vencimento_inicio')) {
            $query->where('data_vencimento', '>=', $request->input('vencimento_inicio'));
        }
        if ($request->filled('vencimento_fim')) {
            $query->where('data_vencimento', '<=', $request->input('vencimento_fim'));
        }

        // Clonando a query *antes* do filtro de status para os contadores
        $queryParaContadores = clone $query;

        // Filtro de Status (pode ser um ou mais)
        if ($request->filled('status') && is_array($request->input('status')) && !empty($request->input('status')[0])) {
            $query->where(function ($q) use ($request) {
                foreach ($request->input('status') as $status) {
                    if ($status === 'pendente') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today());
                        });
                    } elseif ($status === 'vencido') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today());
                        });
                    } elseif ($status === 'pago') {
                        $q->orWhere('status', 'pago');
                    }
                }
            });
        }

        // ================= CÁLCULO DOS KPIs E TOTAIS (baseado na query com filtros) =================
        $kpisQuery = (clone $query)->toBase();

        $kpis = [
            'a_receber' => (clone $kpisQuery)->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today())->sum('valor'),
            'recebido'  => (clone $kpisQuery)->where('status', 'pago')->sum('valor'),
            'vencido'   => (clone $kpisQuery)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->sum('valor'),
            'vence_hoje' => (clone $kpisQuery)->where('status', '!=', 'pago')->whereDate('data_vencimento', today())->sum('valor'),
        ];

        $totalGeralFiltrado = (clone $kpisQuery)->sum('valor');

        // ================= DADOS PARA FILTROS RÁPIDOS (baseado na query *sem* filtro de status) =================
        $contadoresStatus = [
            'pendente' => (clone $queryParaContadores)->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today())->count(),
            'vencido'  => (clone $queryParaContadores)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->count(),
            'pago'     => (clone $queryParaContadores)->where('status', 'pago')->count(),
        ];

        // ================= PAGINAÇÃO =================
        $cobrancas = $query->orderBy('data_vencimento', 'desc')->paginate(15)->withQueryString();

        // Enviando TODAS as variáveis necessárias para a view
        return view('financeiro.contasareceber', compact(
            'cobrancas',
            'kpis',
            'totalGeralFiltrado',
            'contadoresStatus'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MARCAR COMO PAGO
    |--------------------------------------------------------------------------
    */
    public function pagar(Cobranca $cobranca)
    {
        DB::transaction(function () use ($cobranca) {
            $cobranca->update([
                'status'  => 'pago',
                'pago_em' => now(),
            ]);
        });

        return back()->with('success', 'Cobrança marcada como paga.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR COBRANÇA
    |--------------------------------------------------------------------------
    */
    public function destroy(Cobranca $cobranca)
    {
        DB::transaction(function () use ($cobranca) {
            if ($cobranca->orcamento && $cobranca->orcamento->status === 'aguardando_pagamento') {
                $cobranca->orcamento->update(['status' => 'financeiro']);
            }
            $cobranca->delete();
        });

        return back()->with('success', 'Cobrança excluída e devolvida ao financeiro.');
    }
}
