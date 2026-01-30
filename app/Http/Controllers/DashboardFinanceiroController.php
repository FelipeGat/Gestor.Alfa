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

        // DESPESAS POR MÊS
        $despesaPagaPorMes = \App\Models\ContaPagar::whereYear('data_vencimento', $ano)
            ->where('status', 'pago')
            ->selectRaw('MONTH(data_vencimento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $despesaPrevistaPorMes = \App\Models\ContaPagar::whereYear('data_vencimento', $ano)
            ->where('status', 'em_aberto')
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
        $despesaPaga = [];
        $despesaPrevista = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            $labels[]   = Carbon::create()->month($mes)->translatedFormat('M');
            $recebido[] = $recebidoPorMes[$mes] ?? 0;
            $previsto[] = $previstoPorMes[$mes] ?? 0;
            $despesaPaga[] = $despesaPagaPorMes[$mes] ?? 0;
            $despesaPrevista[] = $despesaPrevistaPorMes[$mes] ?? 0;
        }

        // Criar collection para o gráfico
        $financeiroMensal = collect();
        for ($mes = 1; $mes <= 12; $mes++) {
            $financeiroMensal->push([
                'mes' => Carbon::create()->month($mes)->translatedFormat('M'),
                'recebido' => $recebidoPorMes[$mes] ?? 0,
                'previsto' => $previstoPorMes[$mes] ?? 0,
                'despesaPaga' => $despesaPagaPorMes[$mes] ?? 0,
                'despesaPrevista' => $despesaPrevistaPorMes[$mes] ?? 0,
            ]);
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
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        // Agrupar por tipo
        $contasAgrupadasPorTipo = $contasFinanceiras->groupBy('tipo');

        // Saldo total apenas de contas correntes
        $saldoTotalBancos = $contasFinanceiras
            ->where('tipo', 'corrente')
            ->sum('saldo');

        // Processar filtro rápido
        $filtroRapido = $request->get('filtro_rapido', 'mes');

        switch ($filtroRapido) {
            case 'dia':
                $inicio = now()->startOfDay();
                $fim = now()->endOfDay();
                break;
            case 'semana':
                $inicio = now()->startOfWeek();
                $fim = now()->endOfWeek();
                break;
            case 'mes':
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
                break;
            case 'proximo_mes':
                $proximoMes = now()->addMonth();
                $inicio = $proximoMes->copy()->startOfMonth();
                $fim = $proximoMes->copy()->endOfMonth();
                break;
            case 'ano':
                $inicio = now()->startOfYear();
                $fim = now()->endOfYear();
                break;
            case 'custom':
                $inicio = $request->get('inicio')
                    ? Carbon::parse($request->inicio)->startOfDay()
                    : now()->startOfMonth()->startOfDay();
                $fim = $request->get('fim')
                    ? Carbon::parse($request->fim)->endOfDay()
                    : now()->endOfMonth()->endOfDay();
                break;
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        /**
         * RECEITA / DESPESA REALIZADA
         * Usar data_pagamento para filtrar
         */
        $receitaRealizada = Cobranca::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
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

        $despesaRealizada = \App\Models\ContaPagar::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->sum('valor');

        $saldoRealizado = $receitaRealizada - $despesaRealizada;

        /**
         * PREVISTO
         */
        $aReceber = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
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

        $aPagar = \App\Models\ContaPagar::where('status', 'em_aberto')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->sum('valor');

        $saldoPrevisto = $aReceber - $aPagar;

        /**
         * SITUAÇÃO (dentro do período filtrado)
         */
        // Atrasados = contas vencidas antes do período que ainda não foram pagas
        $atrasado = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '<', $inicio)
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

        // Pago = cobranças pagas dentro do período filtrado
        $pago = Cobranca::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
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

        $saldoSituacao = $pago - $atrasado;

        return view('dashboard-financeiro.index', [
            'labels' => $labels,
            'recebido' => $recebido,
            'previsto' => $previsto,
            'despesaPaga' => $despesaPaga,
            'despesaPrevista' => $despesaPrevista,
            'financeiroMensal' => $financeiroMensal,
            'totalRecebido' => $totalRecebido,
            'totalPrevisto' => $totalPrevisto,
            'empresas' => $empresas,
            'empresaId' => $empresaId,
            'ano' => $ano,
            'contasFinanceiras' => $contasFinanceiras,
            'contasAgrupadasPorTipo' => $contasAgrupadasPorTipo,
            'saldoTotalBancos' => $saldoTotalBancos,
            'inicio' => $inicio,
            'fim' => $fim,
            'receitaRealizada' => $receitaRealizada,
            'despesaRealizada' => $despesaRealizada,
            'saldoRealizado' => $saldoRealizado,
            'aReceber' => $aReceber,
            'aPagar' => $aPagar,
            'saldoPrevisto' => $saldoPrevisto,
            'atrasado' => $atrasado,
            'pago' => $pago,
            'saldoSituacao' => $saldoSituacao,
        ]);
    }
}
