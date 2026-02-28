<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Funcionario;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardTecnicoController extends Controller
{
    /**
     * Dashboard principal - Visão geral da operação técnica
     */
    public function index(Request $request)
    {
        // ================= FILTROS =================
        $filtroRapido = $request->get('filtro_rapido', 'mes');
        $empresaId = $request->input('empresa_id');
        $statusFiltro = $request->input('status_atual');

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
                $inicio = $request->get('inicio') ? Carbon::parse($request->get('inicio'))->startOfDay() : now()->startOfMonth();
                $fim = $request->get('fim') ? Carbon::parse($request->get('fim'))->endOfDay() : now()->endOfMonth();
                break;
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        $dataInicio = $inicio;
        $dataFim = $fim;

        $agora = Carbon::now();

        // Query base de atendimentos para o período
        $atendimentosNoPeriodo = Atendimento::whereBetween('data_atendimento', [$dataInicio, $dataFim]);

        if ($empresaId) {
            $atendimentosNoPeriodo->where('empresa_id', $empresaId);
        }
        if ($statusFiltro) {
            $atendimentosNoPeriodo->where('status_atual', $statusFiltro);
        }

        // ================= DADOS PARA OS FILTROS DO CABEÇALHO =================
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();
        $todosStatus = ['aberto', 'em_atendimento', 'finalizacao', 'concluido', 'cancelado'];

        // ================= MÉTRICAS DE RESUMO =================
        $metricasFiltradas = (clone $atendimentosNoPeriodo)->select(DB::raw('COUNT(*) as qtd'))->first();

        // ====== INDICADORES DO TOPO (com filtros aplicados) ======
        $queryCards = Atendimento::whereBetween('data_atendimento', [$dataInicio, $dataFim]);
        if ($empresaId) {
            $queryCards->where('empresa_id', $empresaId);
        }

        $indicadores = [
            // Atendimentos agendados para o período
            'agendados' => (clone $queryCards)
                ->whereIn('status_atual', ['aberto', 'em_atendimento'])
                ->count(),

            // Atendimentos em execução no período
            'em_execucao' => (clone $queryCards)
                ->where('status_atual', 'em_atendimento')
                ->where('em_execucao', true)
                ->whereNotNull('iniciado_em')
                ->count(),

            // Atendimentos em pausa no período
            'em_pausa' => (clone $queryCards)
                ->where('status_atual', 'em_atendimento')
                ->where('em_pausa', true)
                ->count(),

            // Atendimentos finalizados no período
            'finalizados' => (clone $queryCards)
                ->whereIn('status_atual', ['finalizacao', 'concluido'])
                ->count(),

            // Aguardando Finalização (status = finalizacao)
            'aguardando_finalizacao' => (clone $queryCards)
                ->where('status_atual', 'finalizacao')
                ->count(),

            // Técnicos em pausa no período
            'tecnicos_pausados' => (clone $queryCards)
                ->where('status_atual', 'em_atendimento')
                ->where('em_pausa', true)
                ->distinct('funcionario_id')
                ->count('funcionario_id'),
        ];

        // ====== PAINEL DE ACOMPANHAMENTO ======
        $tecnicos = Funcionario::whereHas('user')
            ->whereHas('atendimentos', function ($query) use ($dataInicio, $dataFim, $empresaId, $statusFiltro) {
                $query->whereBetween('data_atendimento', [$dataInicio, $dataFim]);
                if ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                }
                if ($statusFiltro) {
                    $query->where('status_atual', $statusFiltro);
                }
            })
            ->with(['user', 'atendimentos' => function ($query) use ($dataInicio, $dataFim, $empresaId, $statusFiltro) {
                $query->whereBetween('data_atendimento', [$dataInicio, $dataFim]);
                if ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                }
                if ($statusFiltro) {
                    $query->where('status_atual', $statusFiltro);
                }
                $query->with(['cliente', 'empresa', 'assunto', 'pausas.user', 'iniciadoPor'])
                    ->orderByRaw("FIELD(status_atual, 'em_atendimento', 'aberto', 'finalizacao', 'concluido')")
                    ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')");
            }])
            ->get()
            ->map(function ($funcionario) use ($agora) {
                $atendimentosEmAndamento = $funcionario->atendimentos
                    ->where('status_atual', 'em_atendimento')
                    ->whereNotNull('iniciado_em');

                $atendimentoAtual = $atendimentosEmAndamento
                    ->where('em_execucao', true)
                    ->where('em_pausa', false)
                    ->sortByDesc(function ($atendimento) {
                        return optional($atendimento->iniciado_em)->timestamp ?? 0;
                    })
                    ->first();

                if (!$atendimentoAtual) {
                    $atendimentoAtual = $atendimentosEmAndamento
                        ->where('em_pausa', true)
                        ->sortByDesc(function ($atendimento) {
                            return optional($atendimento->iniciado_em)->timestamp ?? 0;
                        })
                        ->first();
                }

                $atendimentosDoPeriodo = $funcionario->atendimentos;
                $totalTempoTrabalhado = 0;
                $totalTempoPausas = 0;

                if ($atendimentoAtual) {
                    $totalTempoTrabalhado = max(0, $atendimentoAtual->tempo_execucao_segundos ?? 0);
                    $totalTempoPausas = max(0, $atendimentoAtual->tempo_pausa_segundos ?? 0);
                }

                // Corrigir tempo trabalhado para atendimento atual em execução
                if ($atendimentoAtual && $atendimentoAtual->em_execucao && !$atendimentoAtual->em_pausa && $atendimentoAtual->iniciado_em) {
                    $diff = $atendimentoAtual->iniciado_em->diffInSeconds($agora, false);
                    if ($diff > 0) {
                        $totalTempoTrabalhado += $diff;
                    }
                }

                $pausaAtiva = null;
                $tempoPausaAtual = 0;
                if ($atendimentoAtual && $atendimentoAtual->em_pausa) {
                    $pausaAtiva = $atendimentoAtual->pausaAtiva();
                    if ($pausaAtiva && $pausaAtiva->iniciada_em) {
                        $diff = $pausaAtiva->iniciada_em->diffInSeconds($agora, false);
                        if ($diff > 0) {
                            $tempoPausaAtual = $diff;
                        }
                    }
                }

                // Nunca mostrar valores negativos
                $totalTempoTrabalhado = max(0, $totalTempoTrabalhado);
                $totalTempoPausas = max(0, $totalTempoPausas);
                $tempoPausaAtual = max(0, $tempoPausaAtual);

                return [
                    'funcionario' => $funcionario,
                    'atendimento_atual' => $atendimentoAtual,
                    'atendimentos_em_andamento_count' => $atendimentosEmAndamento->count(),
                    'pausa_ativa' => $pausaAtiva,
                    'tempo_pausa_atual' => $tempoPausaAtual,
                    'total_tempo_trabalhado' => $totalTempoTrabalhado,
                    'total_tempo_pausas' => $totalTempoPausas,
                    'atendimentos_finalizados' => $atendimentosDoPeriodo
                        ->whereIn('status_atual', ['finalizacao', 'concluido'])
                        ->count(),
                    'atendimentos_total' => $funcionario->atendimentos->count(),
                ];
            });

        // ====== ATENDIMENTOS NÃO INICIADOS ======
        $atendimentosNaoIniciados = Atendimento::whereBetween('data_atendimento', [$dataInicio, $dataFim])
            ->where(function ($query) {
                $query->where('status_atual', 'aberto')
                    ->orWhere(function ($q) {
                        $q->where('status_atual', 'em_atendimento')
                            ->whereNull('iniciado_em');
                    });
            })
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->when($statusFiltro, fn($q) => $q->where('status_atual', $statusFiltro))
            ->with(['funcionario.user', 'cliente', 'empresa'])
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderBy('data_atendimento')
            ->get();

        return view('dashboard-tecnico.index', compact(
            'indicadores',
            'tecnicos',
            'atendimentosNaoIniciados',
            'empresas',
            'todosStatus',
            'empresaId',
            'statusFiltro',
            'filtroRapido',
            'metricasFiltradas',
            'inicio',
            'fim'
        ));
    }

    /**
     * Detalhes de um atendimento específico (Modal/AJAX)
     */
    public function detalhesAtendimento(Atendimento $atendimento)
    {
        $atendimento->load([
            'cliente',
            'empresa',
            'assunto',
            'funcionario.user',
            'andamentos.fotos',
            'pausas.user',
            'pausas.retomadoPor',
            'iniciadoPor',
            'finalizadoPor'
        ]);

        return response()->json([
            'success' => true,
            'atendimento' => $atendimento,
            'html' => view('dashboard-tecnico.partials.modal-detalhes', compact('atendimento'))->render()
        ]);
    }

    /**
     * Lista atendimentos para modal (AJAX)
     */
    public function getAtendimentos(Request $request)
    {
        $filtroRapido = $request->get('filtro_rapido', 'mes');
        $empresaId = $request->input('empresa_id');
        $statusAtual = $request->input('status_atual');

        // Processar filtro de data
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
                $inicio = $request->get('inicio_custom') ? Carbon::parse($request->get('inicio_custom'))->startOfDay() : now()->startOfMonth();
                $fim = $request->get('fim_custom') ? Carbon::parse($request->get('fim_custom'))->endOfDay() : now()->endOfMonth();
                break;
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        $query = Atendimento::whereBetween('data_atendimento', [$inicio, $fim]);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        if ($statusAtual) {
            $query->where('status_atual', $statusAtual);
        }

        try {
            $atendimentos = $query->with(['cliente', 'empresa', 'funcionario', 'funcionario.user', 'assunto'])
                ->orderBy('data_atendimento', 'desc')
                ->get()
                ->map(function ($atendimento) {
                    $tecnico = 'N/D';
                    if (isset($atendimento->funcionario) && isset($atendimento->funcionario->user) && isset($atendimento->funcionario->user->name)) {
                        $tecnico = $atendimento->funcionario->user->name;
                    }
                    return [
                        'id' => $atendimento->id ?? '',
                        'numero' => $atendimento->numero_chamado ?? (isset($atendimento->id) ? '#' . $atendimento->id : 'N/D'),
                        'cliente' => (
                            isset($atendimento->cliente->nome_fantasia) && $atendimento->cliente->nome_fantasia
                            ? $atendimento->cliente->nome_fantasia
                            : (isset($atendimento->cliente->razao_social) && $atendimento->cliente->razao_social
                                ? $atendimento->cliente->razao_social
                                : (isset($atendimento->cliente->nome) && $atendimento->cliente->nome
                                    ? $atendimento->cliente->nome
                                    : 'Sem cliente'))
                        ),
                        'empresa' => isset($atendimento->empresa->nome_fantasia) ? $atendimento->empresa->nome_fantasia : 'N/D',
                        'tecnico' => $tecnico,
                        'assunto' => isset($atendimento->assunto->nome) ? $atendimento->assunto->nome : 'N/D',
                        'prioridade' => $atendimento->prioridade ?? 'N/D',
                        'status' => $atendimento->status_atual ?? 'N/D',
                        'data_atendimento' => isset($atendimento->data_atendimento) ? ($atendimento->data_atendimento ? $atendimento->data_atendimento->format('d/m/Y H:i') : 'N/D') : 'N/D',
                        'tempo_trabalhado' => isset($atendimento->tempo_execucao_segundos) ? gmdate('H:i:s', $atendimento->tempo_execucao_segundos) : '00:00:00',
                        'url' => route('dashboard.tecnico.detalhes', $atendimento->id ?? 0),
                    ];
                });

            return response()->json([
                'success' => true,
                'atendimentos' => $atendimentos,
                'total' => $atendimentos->count()
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erro no getAtendimentos DashboardTecnico: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar atendimentos. Consulte o log do sistema.',
            ], 500);
        }
    }

    /**
     * Atualização em tempo real (Polling/AJAX)
     */
    public function atualizarDados(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $filtroRapido = $request->get('filtro_rapido', 'mes');

        // Processar filtro de data
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
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        $queryCards = Atendimento::whereBetween('data_atendimento', [$inicio, $fim]);
        if ($empresaId) {
            $queryCards->where('empresa_id', $empresaId);
        }

        // Recalcular indicadores
        $indicadores = [
            'agendados' => (clone $queryCards)->whereIn('status_atual', ['aberto', 'em_atendimento'])->count(),
            'em_execucao' => (clone $queryCards)->where('status_atual', 'em_atendimento')->where('em_execucao', true)->whereNotNull('iniciado_em')->count(),
            'em_pausa' => (clone $queryCards)->where('status_atual', 'em_atendimento')->where('em_pausa', true)->count(),
            'finalizados' => (clone $queryCards)->whereIn('status_atual', ['finalizacao', 'concluido'])->count(),
            'tecnicos_ativos' => (clone $queryCards)->where('status_atual', 'em_atendimento')->where('em_execucao', true)->whereNotNull('iniciado_em')->distinct('funcionario_id')->count('funcionario_id'),
            'tecnicos_pausados' => (clone $queryCards)->where('status_atual', 'em_atendimento')->where('em_pausa', true)->distinct('funcionario_id')->count('funcionario_id'),
        ];

        return response()->json([
            'success' => true,
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'indicadores' => $indicadores,
        ]);
    }
}
