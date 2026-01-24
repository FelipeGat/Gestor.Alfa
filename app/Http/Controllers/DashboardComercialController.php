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

        $queryBase = Orcamento::query();

        if ($empresaId) {
            $queryBase->where('empresa_id', $empresaId);
        }

        $totalOrcamentos = (clone $queryBase)->count();

        $qtdAguardando = (clone $queryBase)->where('status', 'aguardando_aprovacao')->count();
        $qtdFinanceiro = (clone $queryBase)->where('status', 'financeiro')->count();
        $qtdAprovado   = (clone $queryBase)->where('status', 'aprovado')->count();
        $qtdAguardandoPagamento   = (clone $queryBase)->where('status', 'aguardando_pagamento')->count();

        $orcamentosPorStatus = (clone $queryBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $orcamentosPorEmpresa = Orcamento::select(
            'empresa_id',
            DB::raw('SUM(valor_total) as total_valor'),
            DB::raw('COUNT(*) as total_qtd')
        )
            ->groupBy('empresa_id')
            ->with('empresa')
            ->get();

        $queryFiltroStatus = (clone $queryBase);
        if ($statusFiltro) {
            $queryFiltroStatus->where('status', $statusFiltro);
        }

        $metricasFiltradas = $queryFiltroStatus->select(
            DB::raw('COUNT(*) as qtd'),
            DB::raw('SUM(valor_total) as valor_total')
        )->first();

        $empresas    = Empresa::orderBy('nome_fantasia')->get();
        $todosStatus = Orcamento::distinct()->pluck('status');

        return view('dashboard-comercial.index', compact(
            'totalOrcamentos',
            'qtdFinanceiro',
            'qtdAguardandoPagamento',
            'qtdAprovado',
            'qtdAguardando',
            'orcamentosPorStatus',
            'orcamentosPorEmpresa',
            'metricasFiltradas',
            'empresas',
            'todosStatus',
            'empresaId',
            'statusFiltro'
        ));
    }
}
