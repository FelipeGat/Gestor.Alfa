<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ContaFinanceira;

class DashboardFinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $request->get('empresa_id');
        $ano = $request->get('ano', now()->year);

        $baseQuery = Cobranca::query()
            ->whereYear('data_vencimento', $ano);

        if ($empresaId) {
            $baseQuery->whereHas(
                'orcamento',
                fn($q) =>
                $q->where('empresa_id', $empresaId)
            );
        }

        /**
         * DADOS AGRUPADOS DO BANCO
         */
        $recebidoPorMes = (clone $baseQuery)
            ->where('status', 'pago')
            ->selectRaw('MONTH(data_vencimento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $previstoPorMes = (clone $baseQuery)
            ->where('status', '!=', 'pago')
            ->selectRaw('MONTH(data_vencimento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        /**
         * MONTA JANEIRO → DEZEMBRO FIXO
         */
        $labels = [];
        $recebido = [];
        $previsto = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            $labels[]   = Carbon::create()->month($mes)->translatedFormat('M');
            $recebido[] = $recebidoPorMes[$mes] ?? 0;
            $previsto[] = $previstoPorMes[$mes] ?? 0;
        }

        /**
         * KPIs
         */
        $totalRecebido = (clone $baseQuery)->where('status', 'pago')->sum('valor');
        $totalPrevisto = (clone $baseQuery)->where('status', '!=', 'pago')->sum('valor');

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        /**
         * SALDOS DAS CONTAS BANCÁRIAS
         */
        $contasFinanceiras = \App\Models\ContaFinanceira::query()
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->orderBy('nome')
            ->get();

        $saldoTotalBancos = $contasFinanceiras->sum('saldo');

        $inicio = $request->get('inicio')
            ? Carbon::parse($request->inicio)->startOfDay()
            : now()->startOfMonth();

        $fim = $request->get('fim')
            ? Carbon::parse($request->fim)->endOfDay()
            : now()->endOfMonth();

        /**
         * RECEITA / DESPESA REALIZADA
         */
        $receitaRealizada = Cobranca::where('status', 'pago')
            ->whereBetween('pago_em', [$inicio, $fim])
            ->when(
                $empresaId,
                fn($q) =>
                $q->whereHas(
                    'orcamento',
                    fn($oq) =>
                    $oq->where('empresa_id', $empresaId)
                )
            )
            ->sum('valor');

        $despesaRealizada = 0; // futuro contas a pagar

        $saldoRealizado = $receitaRealizada - $despesaRealizada;

        /**
         * PREVISTO
         */
        $aReceber = Cobranca::where('status', '!=', 'pago')
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->when(
                $empresaId,
                fn($q) =>
                $q->whereHas(
                    'orcamento',
                    fn($oq) =>
                    $oq->where('empresa_id', $empresaId)
                )
            )
            ->sum('valor');

        $aPagar = 0; // futuro contas a pagar

        $saldoPrevisto = $aReceber - $aPagar;

        /**
         * ATRASADOS / PAGOS
         */
        $atrasado = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '<', now())
            ->when(
                $empresaId,
                fn($q) =>
                $q->whereHas(
                    'orcamento',
                    fn($oq) =>
                    $oq->where('empresa_id', $empresaId)
                )
            )
            ->sum('valor');

        $pago = Cobranca::where('status', 'pago')
            ->whereBetween('pago_em', [$inicio, $fim])
            ->sum('valor');

        $saldoSituacao = $atrasado - $pago;

        return view('dashboard-financeiro.index', compact(
            'labels',
            'recebido',
            'previsto',
            'totalRecebido',
            'totalPrevisto',
            'empresas',
            'empresaId',
            'ano',
            'contasFinanceiras',
            'saldoTotalBancos',
            'inicio',
            'fim',
            'receitaRealizada',
            'despesaRealizada',
            'saldoRealizado',
            'aReceber',
            'aPagar',
            'saldoPrevisto',
            'atrasado',
            'pago',
            'saldoSituacao'
        ));
    }
}
