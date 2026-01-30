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
        $filtroRapido = $request->get('filtro_rapido', 'mes');

        // Processar filtro de data
        $inicio = null;
        $fim = null;

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
            case 'mes_anterior':
                $inicio = now()->subMonth()->startOfMonth();
                $fim = now()->subMonth()->endOfMonth();
                break;
            case 'ano':
                $inicio = now()->startOfYear();
                $fim = now()->endOfYear();
                break;
            case 'custom':
                $inicio = $request->get('inicio') ? \Carbon\Carbon::parse($request->get('inicio'))->startOfDay() : now()->startOfMonth();
                $fim = $request->get('fim') ? \Carbon\Carbon::parse($request->get('fim'))->endOfDay() : now()->endOfMonth();
                break;
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        // Usar query única com cache quando possível
        $queryBase = Orcamento::query()
            ->whereBetween('created_at', [$inicio, $fim]);

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
            'statusFiltro',
            'filtroRapido',
            'inicio',
            'fim'
        ));
    }

    /**
     * Retorna os orçamentos filtrados para exibição no modal
     */
    public function getOrcamentos(Request $request)
    {
        try {
            $empresaId = $request->get('empresa_id');
            $status = $request->get('status');
            $filtroRapido = $request->get('filtro_rapido', 'mes');

            // Processar filtro de data (mesmo do método comercial)
            $inicio = null;
            $fim = null;

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
                case 'mes_anterior':
                    $inicio = now()->subMonth()->startOfMonth();
                    $fim = now()->subMonth()->endOfMonth();
                    break;
                case 'ano':
                    $inicio = now()->startOfYear();
                    $fim = now()->endOfYear();
                    break;
                case 'custom':
                    $inicio = $request->get('inicio') ? \Carbon\Carbon::parse($request->get('inicio'))->startOfDay() : now()->startOfMonth();
                    $fim = $request->get('fim') ? \Carbon\Carbon::parse($request->get('fim'))->endOfDay() : now()->endOfMonth();
                    break;
                default:
                    $inicio = now()->startOfMonth();
                    $fim = now()->endOfMonth();
            }

            $orcamentos = Orcamento::query()
                ->with(['cliente', 'empresa', 'criadoPor'])
                ->whereBetween('created_at', [$inicio, $fim])
                ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($orc) {
                    return [
                        'id' => $orc->id,
                        'cliente' => $orc->cliente ? ($orc->cliente->nome ?? $orc->cliente->razao_social ?? 'N/A') : 'N/A',
                        'empresa' => $orc->empresa ? ($orc->empresa->nome_fantasia ?? 'N/A') : 'N/A',
                        'vendedor' => $orc->criadoPor ? ($orc->criadoPor->name ?? 'N/A') : 'N/A',
                        'valor_total' => number_format($orc->valor_total ?? 0, 2, ',', '.'),
                        'status' => $orc->status,
                        'status_label' => $this->getStatusLabel($orc->status),
                        'data' => $orc->created_at ? $orc->created_at->format('d/m/Y') : 'N/A',
                        'url' => route('orcamentos.imprimir', $orc->id),
                    ];
                });

            // Calcular valor total corretamente
            $valorTotal = 0;
            foreach ($orcamentos as $orc) {
                $valor = str_replace(',', '.', str_replace('.', '', $orc['valor_total']));
                $valorTotal += (float) $valor;
            }

            return response()->json([
                'success' => true,
                'orcamentos' => $orcamentos->values(),
                'total' => $orcamentos->count(),
                'valor_total' => $valorTotal
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar orçamentos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar orçamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar orçamentos filtrados em PDF
     */
    public function exportar(Request $request)
    {
        try {
            $empresaId = $request->get('empresa_id');
            $status = $request->get('status');
            $filtroRapido = $request->get('filtro_rapido', 'mes');

            // Processar filtro de data
            $inicio = null;
            $fim = null;

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
                case 'mes_anterior':
                    $inicio = now()->subMonth()->startOfMonth();
                    $fim = now()->subMonth()->endOfMonth();
                    break;
                case 'ano':
                    $inicio = now()->startOfYear();
                    $fim = now()->endOfYear();
                    break;
                case 'custom':
                    $inicio = $request->get('inicio') ? \Carbon\Carbon::parse($request->get('inicio'))->startOfDay() : now()->startOfMonth();
                    $fim = $request->get('fim') ? \Carbon\Carbon::parse($request->get('fim'))->endOfDay() : now()->endOfMonth();
                    break;
                default:
                    $inicio = now()->startOfMonth();
                    $fim = now()->endOfMonth();
            }

            // Buscar orçamentos com os mesmos filtros do modal
            $orcamentos = Orcamento::query()
                ->with(['cliente', 'empresa', 'criadoPor'])
                ->whereBetween('created_at', [$inicio, $fim])
                ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('created_at', 'desc')
                ->get();

            $valorTotal = $orcamentos->sum('valor_total');

            // Montar título do relatório
            $tituloStatus = $status ? $this->getStatusLabel($status) : 'Todos os Status';
            $periodoTexto = $this->getPeriodoTexto($filtroRapido, $inicio, $fim);

            $empresa = null;
            if ($empresaId) {
                $empresa = Empresa::find($empresaId);
            }

            $pdf = \PDF::loadView('dashboard-comercial.exportar', compact(
                'orcamentos',
                'valorTotal',
                'tituloStatus',
                'periodoTexto',
                'inicio',
                'fim',
                'empresa'
            ));

            $nomeArquivo = 'dashboard_comercial_' . now()->format('Y-m-d_His') . '.pdf';

            return $pdf->download($nomeArquivo);
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar dashboard comercial: ' . $e->getMessage());
            return back()->with('error', 'Erro ao exportar relatório: ' . $e->getMessage());
        }
    }

    private function getPeriodoTexto($filtroRapido, $inicio, $fim)
    {
        switch ($filtroRapido) {
            case 'dia':
                return 'Hoje (' . $inicio->format('d/m/Y') . ')';
            case 'semana':
                return 'Esta Semana (' . $inicio->format('d/m') . ' a ' . $fim->format('d/m/Y') . ')';
            case 'mes':
                return 'Este Mês (' . $inicio->format('m/Y') . ')';
            case 'mes_anterior':
                return 'Mês Anterior (' . $inicio->format('m/Y') . ')';
            case 'ano':
                return 'Este Ano (' . $inicio->format('Y') . ')';
            case 'custom':
                return 'Período: ' . $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');
            default:
                return $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');
        }
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'aguardando_aprovacao' => 'Aguardando Aprovação',
            'financeiro' => 'Financeiro',
            'aprovado' => 'Aprovado',
            'aguardando_pagamento' => 'Aguardando Pagamento',
            'reprovado' => 'Reprovado',
            'cancelado' => 'Cancelado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }
}
