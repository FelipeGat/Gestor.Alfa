<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Empresa;
use App\Models\Cobranca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Segurança (admin ou perfil financeiro)
        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */
        $empresaId    = $request->get('empresa_id');
        $statusFiltro = $request->get('status');

        /*
        |--------------------------------------------------------------------------
        | ================== BLOCO 1 — ORÇAMENTOS (PIPELINE FINANCEIRO)
        |--------------------------------------------------------------------------
        */
        $statusFinanceiros = [
            'financeiro',
            'aguardando_pagamento',
        ];

        $orcamentoQuery = Orcamento::query()
            ->whereIn('status', $statusFinanceiros);

        if ($empresaId) {
            $orcamentoQuery->where('empresa_id', $empresaId);
        }

        if ($statusFiltro) {
            $orcamentoQuery->where('status', $statusFiltro);
        }

        // KPIs
        $qtdAprovado = (clone $orcamentoQuery)->where('status', 'aprovado')->count();
        $qtdFinanceiro = (clone $orcamentoQuery)->where('status', 'financeiro')->count();
        $qtdAguardandoPagamento = (clone $orcamentoQuery)->where('status', 'aguardando_pagamento')->count();

        $valorTotalAberto = (clone $orcamentoQuery)->sum('valor_total');

        $metricasFiltradas = (clone $orcamentoQuery)
            ->selectRaw('COUNT(*) as qtd, SUM(valor_total) as valor_total')
            ->first();

        // Gráfico por status
        $orcamentosPorStatus = (clone $orcamentoQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Gráfico por empresa
        $orcamentosPorEmpresa = (clone $orcamentoQuery)
            ->select(
                'empresa_id',
                DB::raw('COUNT(*) as total_qtd'),
                DB::raw('SUM(valor_total) as total_valor')
            )
            ->groupBy('empresa_id')
            ->with('empresa')
            ->get();

        // Lista para ação do financeiro
        $orcamentosFinanceiro = (clone $orcamentoQuery)
            ->with(['cliente', 'preCliente'])
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ================== BLOCO 2 — COBRANÇAS (FINANCEIRO REAL)
        |--------------------------------------------------------------------------
        */
        $hoje = Carbon::today();

        $cobrancaQuery = Cobranca::with('cliente');

        $totalReceber = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->sum('valor');

        $totalPago = (clone $cobrancaQuery)
            ->where('status', 'pago')
            ->sum('valor');

        $totalVencido = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->sum('valor');

        $venceHoje = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->sum('valor');

        $qtdVencidos = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->count();

        $qtdVenceHoje = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->count();

        $cobrancasPendentes = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | AUXILIARES
        |--------------------------------------------------------------------------
        */
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('financeiro.dashboard', compact(
            // filtros
            'empresas',
            'empresaId',
            'statusFiltro',

            // orçamentos
            'qtdAprovado',
            'qtdFinanceiro',
            'qtdAguardandoPagamento',
            'valorTotalAberto',
            'metricasFiltradas',
            'orcamentosPorStatus',
            'orcamentosPorEmpresa',
            'orcamentosFinanceiro',

            // cobranças
            'totalReceber',
            'totalPago',
            'totalVencido',
            'venceHoje',
            'qtdVencidos',
            'qtdVenceHoje',
            'cobrancasPendentes'
        ));
    }
}
