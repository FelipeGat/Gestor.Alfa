<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Segurança (admin ou perfil financeiro)
        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // ================= FILTROS =================
        $empresaId    = $request->get('empresa_id');
        $statusFiltro = $request->get('status');

        // Status que interessam ao financeiro
        $statusFinanceiros = [
            'aprovado',
            'financeiro',
            'aguardando_pagamento',
        ];

        // ================= QUERY BASE =================
        $query = Orcamento::query()
            ->whereIn('status', $statusFinanceiros);

        // Filtro por empresa
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        // Filtro por status
        if ($statusFiltro) {
            $query->where('status', $statusFiltro);
        }

        // ================= KPIs =================
        $qtdAprovado = (clone $query)
            ->where('status', 'aprovado')
            ->count();

        $qtdFinanceiro = (clone $query)
            ->where('status', 'financeiro')
            ->count();

        $qtdAguardandoPagamento = (clone $query)
            ->where('status', 'aguardando_pagamento')
            ->count();

        $valorTotalAberto = (clone $query)
            ->sum('valor_total');

        // ================= MÉTRICAS FILTRADAS =================
        $metricasFiltradas = (clone $query)
            ->selectRaw('COUNT(*) as qtd, SUM(valor_total) as valor_total')
            ->first();

        // ================= GRÁFICO: STATUS =================
        $orcamentosPorStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ================= GRÁFICO: EMPRESA =================
        $orcamentosPorEmpresa = (clone $query)
            ->select(
                'empresa_id',
                DB::raw('COUNT(*) as total_qtd'),
                DB::raw('SUM(valor_total) as total_valor')
            )
            ->groupBy('empresa_id')
            ->with('empresa')
            ->get();

        // ================= EMPRESAS (FILTRO) =================
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('financeiro.dashboard', compact(
            'empresas',
            'empresaId',
            'statusFiltro',
            'qtdAprovado',
            'qtdFinanceiro',
            'qtdAguardandoPagamento',
            'valorTotalAberto',
            'metricasFiltradas',
            'orcamentosPorStatus',
            'orcamentosPorEmpresa'
        ));
    }
}
