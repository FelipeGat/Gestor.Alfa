<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\AtendimentoPausa;
use App\Models\Funcionario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardTecnicoController extends Controller
{
    /**
     * Dashboard principal - Visão geral da operação técnica
     */
    public function index()
    {
        $hoje = Carbon::today();
        $agora = Carbon::now();

        // ====== INDICADORES DO TOPO ======
        $indicadores = [
            // Atendimentos agendados para hoje
            'agendados_hoje' => Atendimento::whereDate('data_atendimento', $hoje)
                ->whereIn('status_atual', ['aberto', 'em_atendimento'])
                ->count(),

            // Atendimentos em execução no momento
            'em_execucao' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_execucao', true)
                ->whereNotNull('iniciado_em')
                ->count(),

            // Atendimentos em pausa no momento
            'em_pausa' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_pausa', true)
                ->count(),

            // Atendimentos finalizados hoje
            'finalizados_hoje' => Atendimento::whereDate('finalizado_em', $hoje)
                ->whereIn('status_atual', ['finalizacao', 'concluido'])
                ->count(),

            // Técnicos ativos (com atendimento em execução)
            'tecnicos_ativos' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_execucao', true)
                ->whereNotNull('iniciado_em')
                ->distinct('funcionario_id')
                ->count('funcionario_id'),

            // Técnicos em pausa no momento
            'tecnicos_pausados' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_pausa', true)
                ->distinct('funcionario_id')
                ->count('funcionario_id'),
        ];

        // ====== PAINEL DE ACOMPANHAMENTO ======
        // Buscar todos os técnicos que têm atendimentos hoje
        $tecnicos = Funcionario::whereHas('user')
            ->whereHas('atendimentos', function ($query) use ($hoje) {
                $query->where(function ($q) use ($hoje) {
                    // Atendimentos agendados para hoje OU em andamento
                    $q->whereDate('data_atendimento', $hoje)
                        ->orWhere(function ($qq) {
                            $qq->where('status_atual', 'em_atendimento')
                                ->whereNotNull('iniciado_em');
                        });
                });
            })
            ->with(['user', 'atendimentos' => function ($query) use ($hoje) {
                $query->where(function ($q) use ($hoje) {
                    $q->whereDate('data_atendimento', $hoje)
                        ->orWhere(function ($qq) {
                            $qq->where('status_atual', 'em_atendimento')
                                ->whereNotNull('iniciado_em');
                        });
                })
                    ->with(['cliente', 'empresa', 'assunto', 'pausas.user', 'iniciadoPor'])
                    ->orderByRaw("FIELD(status_atual, 'em_atendimento', 'aberto', 'finalizacao', 'concluido')")
                    ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')");
            }])
            ->get()
            ->map(function ($funcionario) use ($agora) {
                // Buscar atendimento atual (em andamento)
                $atendimentoAtual = $funcionario->atendimentos
                    ->where('status_atual', 'em_atendimento')
                    ->whereNotNull('iniciado_em')
                    ->first();

                // Calcular totais do dia
                $atendimentosDoDia = $funcionario->atendimentos
                    ->whereIn('status_atual', ['em_atendimento', 'finalizacao', 'concluido']);

                $totalTempoTrabalhado = $atendimentosDoDia->sum('tempo_execucao_segundos');
                $totalTempoPausas = $atendimentosDoDia->sum('tempo_pausa_segundos');

                // Se tem atendimento em execução, adicionar tempo atual
                if ($atendimentoAtual && $atendimentoAtual->em_execucao) {
                    $ultimaPausa = $atendimentoAtual->pausas()
                        ->whereNotNull('encerrada_em')
                        ->latest('encerrada_em')
                        ->first();

                    $inicioContagem = $ultimaPausa ? $ultimaPausa->encerrada_em : $atendimentoAtual->iniciado_em;
                    $totalTempoTrabalhado += $agora->diffInSeconds($inicioContagem);
                }

                // Se tem pausa ativa, calcular tempo da pausa
                $pausaAtiva = null;
                $tempoPausaAtual = 0;
                if ($atendimentoAtual && $atendimentoAtual->em_pausa) {
                    $pausaAtiva = $atendimentoAtual->pausaAtiva();
                    if ($pausaAtiva) {
                        $tempoPausaAtual = $agora->diffInSeconds($pausaAtiva->iniciada_em);
                    }
                }

                return [
                    'funcionario' => $funcionario,
                    'atendimento_atual' => $atendimentoAtual,
                    'pausa_ativa' => $pausaAtiva,
                    'tempo_pausa_atual' => $tempoPausaAtual,
                    'total_tempo_trabalhado' => $totalTempoTrabalhado,
                    'total_tempo_pausas' => $totalTempoPausas,
                    'atendimentos_finalizados_hoje' => $atendimentosDoDia
                        ->whereIn('status_atual', ['finalizacao', 'concluido'])
                        ->count(),
                    'atendimentos_total_hoje' => $funcionario->atendimentos->count(),
                ];
            });

        // ====== ATENDIMENTOS NÃO INICIADOS ======
        $atendimentosNaoIniciados = Atendimento::whereDate('data_atendimento', $hoje)
            ->where('status_atual', 'aberto')
            ->orWhere(function ($query) {
                $query->where('status_atual', 'em_atendimento')
                    ->whereNull('iniciado_em');
            })
            ->with(['funcionario.user', 'cliente', 'empresa'])
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderBy('data_atendimento')
            ->get();

        return view('dashboard-tecnico.index', compact('indicadores', 'tecnicos', 'atendimentosNaoIniciados'));
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
     * Atualização em tempo real (Polling/AJAX)
     */
    public function atualizarDados()
    {
        $hoje = Carbon::today();
        $agora = Carbon::now();

        // Recalcular indicadores
        $indicadores = [
            'agendados_hoje' => Atendimento::whereDate('data_atendimento', $hoje)
                ->whereIn('status_atual', ['aberto', 'em_atendimento'])
                ->count(),
            'em_execucao' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_execucao', true)
                ->whereNotNull('iniciado_em')
                ->count(),
            'em_pausa' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_pausa', true)
                ->count(),
            'finalizados_hoje' => Atendimento::whereDate('finalizado_em', $hoje)
                ->whereIn('status_atual', ['finalizacao', 'concluido'])
                ->count(),
            'tecnicos_ativos' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_execucao', true)
                ->whereNotNull('iniciado_em')
                ->distinct('funcionario_id')
                ->count('funcionario_id'),
            'tecnicos_pausados' => Atendimento::where('status_atual', 'em_atendimento')
                ->where('em_pausa', true)
                ->distinct('funcionario_id')
                ->count('funcionario_id'),
        ];

        // Buscar status dos técnicos
        $tecnicos = Funcionario::whereHas('user')
            ->whereHas('atendimentos', function ($query) use ($hoje) {
                $query->where(function ($q) use ($hoje) {
                    $q->whereDate('data_atendimento', $hoje)
                        ->orWhere(function ($qq) {
                            $qq->where('status_atual', 'em_atendimento')
                                ->whereNotNull('iniciado_em');
                        });
                });
            })
            ->with(['user', 'atendimentos' => function ($query) use ($hoje) {
                $query->where(function ($q) use ($hoje) {
                    $q->whereDate('data_atendimento', $hoje)
                        ->orWhere(function ($qq) {
                            $qq->where('status_atual', 'em_atendimento')
                                ->whereNotNull('iniciado_em');
                        });
                })
                    ->with(['cliente', 'empresa', 'pausas']);
            }])
            ->get()
            ->map(function ($funcionario) use ($agora) {
                $atendimentoAtual = $funcionario->atendimentos
                    ->where('status_atual', 'em_atendimento')
                    ->whereNotNull('iniciado_em')
                    ->first();

                $pausaAtiva = null;
                if ($atendimentoAtual && $atendimentoAtual->em_pausa) {
                    $pausaAtiva = $atendimentoAtual->pausaAtiva();
                }

                return [
                    'funcionario_id' => $funcionario->id,
                    'nome' => $funcionario->user->name,
                    'status' => $atendimentoAtual ? ($atendimentoAtual->em_pausa ? 'pausa' : 'execucao') : 'livre',
                    'cliente' => $atendimentoAtual ? ($atendimentoAtual->cliente->nome_fantasia ?? 'Sem cliente') : null,
                    'tipo_pausa' => $pausaAtiva ? $pausaAtiva->tipo_pausa_label : null,
                    'atendimento_id' => $atendimentoAtual->id ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'timestamp' => $agora->format('d/m/Y H:i:s'),
            'indicadores' => $indicadores,
            'tecnicos' => $tecnicos,
        ]);
    }

    /**
     * Formatar segundos em HH:MM:SS
     */
    private function formatarTempo($segundos)
    {
        $segundos = max(0, $segundos);
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }
}
