<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\Assunto;
use App\Models\Atendimento;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use App\Models\Orcamento;
use Illuminate\Http\Request;

class DashboardComercialController extends Controller
{
    public function index(Request $request)
    {
        return $this->comercial($request);
    }

    public function comercial(Request $request)
    {
        $empresaId    = $request->get('empresa_id');
        $statusFiltro = $request->get('status');

        // Usar query única com cache quando possível
        $queryBase = Orcamento::query();

        if ($empresaId) {
            $queryBase->where('empresa_id', $empresaId);
        }

        // Executar uma única query para todas as contagens por status
        $statusCount = (clone $queryBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalOrcamentos = $statusCount->sum();
        $qtdAguardando = $statusCount->get('aguardando_aprovacao', 0);
        $qtdFinanceiro = $statusCount->get('financeiro', 0);
        $qtdAprovado   = $statusCount->get('aprovado', 0);
        $qtdAguardandoPagamento = $statusCount->get('aguardando_pagamento', 0);

        // Métricas por empresa com eager loading
        $orcamentosPorEmpresa = Orcamento::select(
            'empresa_id',
            DB::raw('SUM(valor_total) as total_valor'),
            DB::raw('COUNT(*) as total_qtd')
        )
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->groupBy('empresa_id')
            ->with(['empresa:id,nome_fantasia'])
            ->get();

        // Métricas filtradas por status
        $queryFiltroStatus = (clone $queryBase);
        if ($statusFiltro) {
            $queryFiltroStatus->where('status', $statusFiltro);
        }

        $metricasFiltradas = $queryFiltroStatus->select(
            DB::raw('COUNT(*) as qtd'),
            DB::raw('SUM(valor_total) as valor_total')
        )->first();

        $empresas = Empresa::select('id', 'nome_fantasia')
            ->orderBy('nome_fantasia')
            ->get();

        $todosStatus = Orcamento::distinct()->pluck('status');

        return view('dashboard-comercial.index', compact(
            'totalOrcamentos',
            'qtdFinanceiro',
            'qtdAguardandoPagamento',
            'qtdAprovado',
            'qtdAguardando',
            'statusCount',
            'orcamentosPorEmpresa',
            'metricasFiltradas',
            'empresas',
            'todosStatus',
            'empresaId',
            'statusFiltro'
        ));
    }
}
