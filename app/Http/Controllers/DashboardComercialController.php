<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\Assunto;
use App\Models\Atendimento;
use App\Models\ContaPagar;
use App\Models\Empresa;
use App\Models\MetaComercial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Orcamento;
use App\Models\OrcamentoHistorico;
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
        $origemFiltro = $request->get('origem', 'todos');

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
        // Filtro de origem: contrato = tem atendimento_id, avulso = atendimento_id null
        if ($origemFiltro === 'contrato') {
            $queryBase->whereNotNull('atendimento_id');
        } elseif ($origemFiltro === 'avulso') {
            $queryBase->whereNull('atendimento_id');
        }

        // Executar uma única query para todas as contagens por status
        $statusCount = (clone $queryBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalOrcamentos = $statusCount->sum();
        $qtdEmElaboracao = $statusCount->get('em_elaboracao', 0);
        $qtdAguardando   = $statusCount->get('aguardando_aprovacao', 0);
        $qtdAprovado     = $statusCount->get('aprovado', 0);
        $qtdEmAndamento  = $statusCount->get('em_andamento', 0);
        $qtdConcluido    = $statusCount->get('concluido', 0);

        // Métricas por empresa com eager loading
        $orcamentosPorEmpresa = Orcamento::select(
            'empresa_id',
            DB::raw('SUM(valor_total) as total_valor'),
            DB::raw('COUNT(*) as total_qtd')
        )
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            // Filtro de origem também para os cards de empresa
            ->when($origemFiltro === 'contrato', function ($query) {
                $query->whereNotNull('atendimento_id');
            })
            ->when($origemFiltro === 'avulso', function ($query) {
                $query->whereNull('atendimento_id');
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

        // Breakdown por empresa + status para o gráfico de volume financeiro
        // Sem filtro de data, igual ao $orcamentosPorEmpresa, para exibir todas as empresas
        $orcamentosPorEmpresaStatus = Orcamento::select(
                'empresa_id',
                'status',
                DB::raw('SUM(valor_total) as total_valor')
            )
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->when($origemFiltro === 'contrato', function ($query) {
                $query->whereNotNull('atendimento_id');
            })
            ->when($origemFiltro === 'avulso', function ($query) {
                $query->whereNull('atendimento_id');
            })
            ->groupBy('empresa_id', 'status')
            ->with(['empresa:id,nome_fantasia'])
            ->get();

        $empresas = Empresa::select('id', 'nome_fantasia')
            ->orderBy('nome_fantasia')
            ->get();

        $todosStatus = Orcamento::distinct()->pluck('status');

        // ===================== A PAGAR — PRÓXIMO MÊS =====================
        $proximoMesRef  = now()->addMonth();
        $inicioProxMes  = $proximoMesRef->copy()->startOfMonth();
        $fimProxMes     = $proximoMesRef->copy()->endOfMonth();
        $nomeProximoMes = ucfirst($proximoMesRef->translatedFormat('F'));

        $diasUteisProxMes = 0;
        $dIter = $inicioProxMes->copy();
        while ($dIter->lte($fimProxMes)) {
            if ($dIter->isWeekday()) $diasUteisProxMes++;
            $dIter->addDay();
        }

        $aPagarProxMes = (float) ContaPagar::where('status', 'em_aberto')
            ->whereDate('data_vencimento', '>=', $inicioProxMes->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fimProxMes->format('Y-m-d'))
            ->when($empresaId, fn ($q) => $q->whereHas('centroCusto', fn ($cq) => $cq->where('empresa_id', $empresaId)))
            ->sum('valor');

        $ticketMedioProxMes = $diasUteisProxMes > 0
            ? round($aPagarProxMes / $diasUteisProxMes, 2)
            : 0;

        // A Pagar por empresa (próximo mês)
        $aPagarProxMesPorEmpresa = $empresas->map(function ($emp) use ($inicioProxMes, $fimProxMes, $diasUteisProxMes) {
            $valor = (float) ContaPagar::where('status', 'em_aberto')
                ->whereDate('data_vencimento', '>=', $inicioProxMes->format('Y-m-d'))
                ->whereDate('data_vencimento', '<=', $fimProxMes->format('Y-m-d'))
                ->whereHas('centroCusto', fn ($cq) => $cq->where('empresa_id', $emp->id))
                ->sum('valor');
            return (object) [
                'nome'          => $emp->nome_fantasia,
                'a_pagar'       => $valor,
                'ticket_dia'    => $diasUteisProxMes > 0 ? round($valor / $diasUteisProxMes, 2) : 0,
            ];
        })->filter(fn ($e) => $e->a_pagar > 0)->values();

        // ===================== META × REALIZADO (mês corrente) =====================
        $mesAtual = now()->month;
        $anoAtual = now()->year;

        $realizadoRaw = Orcamento::whereIn('status', ['concluido', 'aprovado', 'em_andamento', 'financeiro'])
            ->whereMonth('created_at', $mesAtual)
            ->whereYear('created_at', $anoAtual)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->select('vendedor_id', 'empresa_id', DB::raw('SUM(valor_total) as total'))
            ->groupBy('vendedor_id', 'empresa_id')
            ->with(['vendedor:id,name', 'empresa:id,nome_fantasia'])
            ->get();

        $metasMes = MetaComercial::where('mes', $mesAtual)
            ->where('ano', $anoAtual)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->with(['empresa:id,nome_fantasia', 'user:id,name'])
            ->get();

        // Estrutura para o JS: por vendedor
        $vendedoresMap = [];
        foreach ($realizadoRaw as $row) {
            $uid  = $row->vendedor_id ?? 0;
            $nome = $row->vendedor?->name ?? 'Sem Vendedor';
            if (!isset($vendedoresMap[$uid])) {
                $vendedoresMap[$uid] = ['nome' => $nome, 'realizado' => 0, 'por_empresa' => []];
            }
            $empNome = $row->empresa?->nome_fantasia ?? 'Sem Empresa';
            $vendedoresMap[$uid]['realizado']              += (float) $row->total;
            $vendedoresMap[$uid]['por_empresa'][$empNome]  = ($vendedoresMap[$uid]['por_empresa'][$empNome] ?? 0) + (float) $row->total;
        }
        foreach ($metasMes as $meta) {
            $uid = $meta->user_id ?? 0;
            if (!isset($vendedoresMap[$uid])) {
                $nome = $meta->user?->name ?? 'Sem Vendedor';
                $vendedoresMap[$uid] = ['nome' => $nome, 'realizado' => 0, 'por_empresa' => []];
            }
            $vendedoresMap[$uid]['meta'] = (float) $meta->valor_meta;
        }

        $metaRealizadoChart = array_values($vendedoresMap);

        // Vendedores ativos (com orçamentos no mês ou com meta definida) para o form de metas
        $vendedoresAtivos = \App\Models\User::whereIn('id', array_filter(array_keys($vendedoresMap)))
            ->orWhereIn('tipo', ['comercial', 'admin'])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('dashboard-comercial.index', compact(
            'totalOrcamentos',
            'qtdEmElaboracao',
            'qtdAguardando',
            'qtdAprovado',
            'qtdEmAndamento',
            'qtdConcluido',
            'statusCount',
            'orcamentosPorEmpresa',
            'orcamentosPorEmpresaStatus',
            'metricasFiltradas',
            'empresas',
            'todosStatus',
            'empresaId',
            'statusFiltro',
            'filtroRapido',
            'inicio',
            'fim',
            'origemFiltro',
            // A Pagar Próximo Mês
            'nomeProximoMes',
            'diasUteisProxMes',
            'aPagarProxMes',
            'ticketMedioProxMes',
            'aPagarProxMesPorEmpresa',
            // Meta × Realizado
            'metaRealizadoChart',
            'metasMes',
            'vendedoresAtivos',
            'mesAtual',
            'anoAtual'
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
            $origemFiltro = $request->get('origem', 'todos');

            // Log para debug
            Log::info('getOrcamentos chamado', [
                'empresa_id' => $empresaId,
                'status' => $status,
                'filtro_rapido' => $filtroRapido
            ]);

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
                ->with(['cliente', 'preCliente', 'empresa', 'criadoPor'])
                ->whereBetween('created_at', [$inicio, $fim])
                ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($origemFiltro === 'contrato', fn($q) => $q->whereNotNull('atendimento_id'))
                ->when($origemFiltro === 'avulso', fn($q) => $q->whereNull('atendimento_id'))
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Query executada', [
                'inicio' => $inicio,
                'fim' => $fim,
                'total_encontrado' => $orcamentos->count()
            ]);

            $orcamentos = $orcamentos->map(function ($orc) {
                $nomeVendedor = $orc->criadoPor->name ?? null;
                if ($nomeVendedor) {
                    $partes = explode(' ', trim($nomeVendedor));
                    $nomeVendedor = count($partes) > 1
                        ? $partes[0] . ' ' . end($partes)
                        : $partes[0];
                }

                return [
                    'id' => $orc->id,
                    'numero' => $orc->numero_orcamento,
                    'cliente' => $orc->nome_cliente !== '—' ? $orc->nome_cliente : 'N/A',
                    'empresa' => $orc->empresa ? ($orc->empresa->nome_fantasia ?? 'N/A') : 'N/A',
                    'vendedor' => $nomeVendedor ?? 'N/A',
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
            Log::error('Erro ao buscar orçamentos: ' . $e->getMessage());
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
            $origemFiltro = $request->get('origem', 'todos');

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
                ->with(['cliente', 'preCliente', 'empresa', 'criadoPor'])
                ->whereBetween('created_at', [$inicio, $fim])
                ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($origemFiltro === 'contrato', fn($q) => $q->whereNotNull('atendimento_id'))
                ->when($origemFiltro === 'avulso', fn($q) => $q->whereNull('atendimento_id'))
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

            $pdf = Pdf::loadView('dashboard-comercial.exportar', compact(
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
            Log::error('Erro ao exportar dashboard comercial: ' . $e->getMessage());
            return back()->with('error', 'Erro ao exportar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Salva ou atualiza uma meta comercial mensal
     */
    public function salvarMeta(Request $request)
    {
        try {
            $request->validate([
                'empresa_id' => 'required|exists:empresas,id',
                'user_id'    => 'nullable|exists:users,id',
                'mes'        => 'required|integer|min:1|max:12',
                'ano'        => 'required|integer|min:2020|max:2099',
                'valor_meta' => 'required|numeric|min:0',
            ]);

            MetaComercial::updateOrCreate(
                [
                    'empresa_id' => $request->empresa_id,
                    'user_id'    => $request->user_id ?: null,
                    'mes'        => $request->mes,
                    'ano'        => $request->ano,
                ],
                ['valor_meta' => $request->valor_meta]
            );

            return response()->json(['success' => true, 'message' => 'Meta salva com sucesso!']);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar meta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao salvar meta.'], 500);
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
            'em_elaboracao'       => 'Em Elaboração',
            'aguardando_aprovacao' => 'Aguardando Aprovação',
            'aprovado'            => 'Aprovado',
            'em_andamento'        => 'Em Andamento',
            'concluido'           => 'Concluído',
            'financeiro'          => 'Financeiro',
            'aguardando_pagamento' => 'Aguardando Pagamento',
            'reprovado'           => 'Reprovado',
            'cancelado'           => 'Cancelado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Retorna os históricos de um orçamento
     */
    public function getHistoricos($orcamentoId)
    {
        try {
            $orcamento = Orcamento::findOrFail($orcamentoId);

            $historicos = $orcamento->historicos()
                ->with('user:id,name')
                ->get()
                ->map(function ($hist) {
                    return [
                        'id' => $hist->id,
                        'observacao' => $hist->observacao,
                        'usuario' => $hist->user ? $hist->user->name : 'N/A',
                        'data' => $hist->created_at->format('d/m/Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'historicos' => $historicos,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar históricos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar históricos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adiciona um novo histórico ao orçamento
     */
    public function adicionarHistorico(Request $request, $orcamentoId)
    {
        try {
            $request->validate([
                'observacao' => 'required|string|max:5000'
            ]);

            $orcamento = Orcamento::findOrFail($orcamentoId);

            // @phpstan-ignore-next-line
            $historico = OrcamentoHistorico::create([
                'orcamento_id' => $orcamento->id,
                'user_id' => Auth::id() ?? 0,
                'observacao' => $request->observacao,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Histórico adicionado com sucesso!',
                'historico' => [
                    'id' => $historico->id,
                    'observacao' => $historico->observacao,
                    // @phpstan-ignore-next-line
                    'usuario' => Auth::user()?->name ?? 'N/A',
                    'data' => $historico->created_at->format('d/m/Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar histórico: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar histórico: ' . $e->getMessage()
            ], 500);
        }
    }
}
