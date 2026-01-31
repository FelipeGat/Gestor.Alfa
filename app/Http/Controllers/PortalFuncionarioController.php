<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\AtendimentoPausa;
use App\Models\AtendimentoAndamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PortalFuncionarioController extends Controller
{
    /**
     * Tela inicial do portal - 3 botões principais
     */
    public function index()
    {
        $funcionarioId = Auth::user()->funcionario_id;

        // Estatísticas rápidas
        $totalAbertos = Atendimento::where('funcionario_id', $funcionarioId)
            ->whereIn('status_atual', ['aberto', 'em_atendimento'])
            ->count();

        $totalFinalizados = Atendimento::where('funcionario_id', $funcionarioId)
            ->where('status_atual', 'concluido')
            ->count();

        // Atendimentos em execução ou pausados
        $totalEmAtendimento = Atendimento::where('funcionario_id', $funcionarioId)
            ->where('status_atual', 'em_atendimento')
            ->whereNotNull('iniciado_em')
            ->count();

        // Verifica se tem algum atendimento pausado
        $temPausado = Atendimento::where('funcionario_id', $funcionarioId)
            ->where('em_pausa', true)
            ->exists();

        return view('portal-funcionario.index', compact('totalAbertos', 'totalFinalizados', 'totalEmAtendimento', 'temPausado'));
    }

    /**
     * Painel de Chamados - Lista cards organizados por status e prioridade
     */
    public function chamados()
    {
        $funcionarioId = Auth::user()->funcionario_id;

        // Buscar atendimentos organizados por status
        // Abertos: inclui também atendimentos 'em_atendimento' sem iniciado_em (antigos)
        $abertos = Atendimento::with(['cliente', 'empresa', 'assunto'])
            ->where('funcionario_id', $funcionarioId)
            ->where(function ($query) {
                $query->where('status_atual', 'aberto')
                    ->orWhere(function ($q) {
                        $q->where('status_atual', 'em_atendimento')
                            ->whereNull('iniciado_em');
                    });
            })
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderBy('data_atendimento', 'asc')
            ->get();

        // Em Atendimento: apenas os que foram iniciados pelo novo sistema
        $emAtendimento = Atendimento::with(['cliente', 'empresa', 'assunto'])
            ->where('funcionario_id', $funcionarioId)
            ->where('status_atual', 'em_atendimento')
            ->whereNotNull('iniciado_em')
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderBy('iniciado_em', 'desc')
            ->get();

        $finalizados = Atendimento::with(['cliente', 'empresa', 'assunto'])
            ->where('funcionario_id', $funcionarioId)
            ->whereIn('status_atual', ['finalizacao', 'concluido'])
            ->orderBy('finalizado_em', 'desc')
            ->limit(20)
            ->get();

        // Próximo da fila (primeiro aberto por prioridade)
        $proximoFila = $abertos->first();

        return view('portal-funcionario.chamados', compact('abertos', 'emAtendimento', 'finalizados', 'proximoFila'));
    }

    /**
     * Visualizar detalhes do atendimento
     */
    public function showAtendimento(Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        // Verificar se o atendimento pertence ao funcionário
        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);

        $atendimento = Atendimento::with([
            'cliente',
            'empresa',
            'assunto',
            'andamentos.fotos',
            'pausas.user',
            'pausas.retomadoPor',
            'iniciadoPor',
            'finalizadoPor'
        ])->findOrFail($atendimento->id);

        return view('portal-funcionario.atendimento-detalhes', compact('atendimento'));
    }

    /**
     * Iniciar atendimento - Exige 3 fotos
     */
    public function iniciarAtendimento(Request $request, Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);

        // Permitir iniciar se status é 'aberto' OU 'em_atendimento' sem iniciado_em (atendimentos antigos)
        if ($atendimento->status_atual !== 'aberto' && $atendimento->iniciado_em !== null) {
            return back()->with('error', 'Este atendimento já foi iniciado.');
        }

        // Verificar se já existe atendimento em execução de outro cliente
        $atendimentoEmExecucao = Atendimento::where('funcionario_id', $funcionarioId)
            ->where('status_atual', 'em_atendimento')
            ->where('em_execucao', true)
            ->whereNotNull('iniciado_em')
            ->where('id', '!=', $atendimento->id)
            ->where('cliente_id', '!=', $atendimento->cliente_id)
            ->exists();

        if ($atendimentoEmExecucao) {
            return back()->with('error', 'Você já possui um atendimento em execução de outro cliente. Finalize-o antes de iniciar um novo.');
        }

        // Validar 3 fotos obrigatórias
        $request->validate([
            'fotos' => 'required|array|min:3|max:3',
            'fotos.*' => 'required|image|max:5120', // 5MB max
        ], [
            'fotos.required' => 'É obrigatório enviar 3 fotos para iniciar o atendimento',
            'fotos.min' => 'É obrigatório enviar exatamente 3 fotos',
            'fotos.max' => 'É obrigatório enviar exatamente 3 fotos',
        ]);

        DB::beginTransaction();
        try {
            // Atualizar atendimento
            $atendimento->update([
                'status_atual' => 'em_atendimento',
                'iniciado_em' => now(),
                'iniciado_por_user_id' => Auth::id(),
                'em_execucao' => true,
                'em_pausa' => false,
            ]);

            // Criar andamento com fotos
            $andamento = $atendimento->andamentos()->create([
                'user_id' => Auth::id(),
                'descricao' => 'Atendimento iniciado pelo técnico',
            ]);

            // Salvar as 3 fotos
            foreach ($request->file('fotos') as $index => $foto) {
                $path = $foto->store('atendimentos/fotos', 'public');
                $andamento->fotos()->create([
                    'arquivo' => $path,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('portal-funcionario.atendimento.show', $atendimento)
                ->with('success', '✅ Atendimento iniciado! Execução em andamento. Bom trabalho!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao iniciar atendimento: ' . $e->getMessage());
        }
    }

    /**
     * Pausar atendimento - Exige tipo e 1 foto
     */
    public function pausarAtendimento(Request $request, Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);
        abort_if(!$atendimento->em_execucao, 400, 'Atendimento não está em execução');
        abort_if($atendimento->em_pausa, 400, 'Atendimento já está pausado');

        $regras = [
            'tipo_pausa' => 'required|in:almoco,deslocamento,material,fim_dia',
        ];
        $mensagens = [
            'tipo_pausa.required' => 'Selecione o tipo de pausa',
        ];
        // Só exige foto se não for material
        if ($request->tipo_pausa !== 'material') {
            $regras['foto'] = 'required|image|max:5120';
            $mensagens['foto.required'] = 'É obrigatório enviar 1 foto ao pausar';
        }
        $request->validate($regras, $mensagens);

        DB::beginTransaction();
        try {
            // Calcular tempo decorrido desde o início ou última retomada usando timestamps
            $ultimaPausa = $atendimento->pausas()->whereNotNull('encerrada_em')->latest('encerrada_em')->first();
            $inicioContagem = $ultimaPausa ? $ultimaPausa->encerrada_em : $atendimento->iniciado_em;
            $agora = now();
            // Auditoria: log para depuração
            \Log::info('[PAUSAR] agora=' . $agora . ' | inicioContagem=' . $inicioContagem . ' | tempo_execucao_segundos=' . $atendimento->tempo_execucao_segundos);
            if (!$inicioContagem) {
                // Não deve acontecer, mas se acontecer, não incrementa nada
                $tempoDecorrido = 0;
            } else {
                $tempoDecorrido = max(0, $agora->timestamp - $inicioContagem->timestamp);
            }
            $novoTempoExecucao = max(0, ($atendimento->tempo_execucao_segundos ?? 0) + $tempoDecorrido);
            $atendimento->update([
                'tempo_execucao_segundos' => $novoTempoExecucao,
                'em_execucao' => false,
                'em_pausa' => true,
            ]);
            $atendimento->refresh();
            \Log::info('[PAUSAR] tempoDecorrido=' . $tempoDecorrido . ' | novoTempoExecucao=' . $novoTempoExecucao);

            // Salvar foto
            $fotoPath = $request->file('foto')->store('atendimentos/pausas', 'public');

            // Criar registro de pausa
            AtendimentoPausa::create([
                'atendimento_id' => $atendimento->id,
                'user_id' => Auth::id(),
                'tipo_pausa' => $request->tipo_pausa,
                'iniciada_em' => $agora,
                'foto_inicio_path' => $fotoPath,
            ]);

            DB::commit();

            $tipoLabel = [
                'almoco' => 'Almoço',
                'deslocamento' => 'Deslocamento entre Clientes',
                'material' => 'Compra de Material',
                'fim_dia' => 'Encerramento do Dia',
            ][$request->tipo_pausa] ?? 'Pausa';

            return back()->with('success', '⏸️ Atendimento pausado. Cronômetro de pausa iniciado para: ' . $tipoLabel);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao pausar atendimento: ' . $e->getMessage());
        }
    }

    /**
     * Retomar atendimento - Exige 1 foto
     */
    public function retomarAtendimento(Request $request, Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);
        abort_if(!$atendimento->em_pausa, 400, 'Atendimento não está pausado');

        $regras = [];
        $mensagens = [];
        // Só exige foto se não for material
        $pausaAtiva = $atendimento->pausaAtiva();
        if ($pausaAtiva && $pausaAtiva->tipo_pausa !== 'material') {
            $regras['foto'] = 'required|image|max:5120';
            $mensagens['foto.required'] = 'É obrigatório enviar 1 foto ao retomar';
        }
        $request->validate($regras, $mensagens);

        if (!$pausaAtiva) {
            return back()->with('error', 'Nenhuma pausa ativa encontrada');
        }

        DB::beginTransaction();
        try {
            // Salvar foto de retorno se enviada
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('atendimentos/pausas', 'public');
                $pausaAtiva->foto_retorno_path = $fotoPath;
            }
            $pausaAtiva->retomado_por_user_id = Auth::id();
            // Encerrar pausa e calcular tempo
            $pausaAtiva->encerrar();
            // Atualizar atendimento
            $novoTempoPausa = max(0, ($atendimento->tempo_pausa_segundos ?? 0) + ($pausaAtiva->tempo_segundos ?? 0));
            $atendimento->update([
                'tempo_pausa_segundos' => $novoTempoPausa,
                'iniciado_em' => now(), // Sempre que retoma, novo início de execução
                'em_execucao' => true,
                'em_pausa' => false,
            ]);
            $atendimento->refresh();
            DB::commit();
            return back()->with('success', 'Atendimento retomado. Cronômetro de execução reiniciado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao retomar atendimento: ' . $e->getMessage());
        }
    }

    /**
     * Finalizar atendimento - Exige 3 fotos
     */
    public function finalizarAtendimento(Request $request, Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);
        abort_if($atendimento->status_atual !== 'em_atendimento', 400, 'Atendimento não está em execução');

        $request->validate([
            'fotos' => 'required|array|min:3|max:3',
            'fotos.*' => 'required|image|max:5120',
            'observacao' => 'nullable|string|max:1000',
        ], [
            'fotos.required' => 'É obrigatório enviar 3 fotos para finalizar o atendimento',
            'fotos.min' => 'É obrigatório enviar exatamente 3 fotos',
            'fotos.max' => 'É obrigatório enviar exatamente 3 fotos',
        ]);

        DB::beginTransaction();
        try {
            // Se estava em execução, calcular tempo final usando timestamps
            if ($atendimento->em_execucao) {
                $ultimaPausa = $atendimento->pausas()->whereNotNull('encerrada_em')->latest('encerrada_em')->first();
                $inicioContagem = $ultimaPausa ? $ultimaPausa->encerrada_em : $atendimento->iniciado_em;
                $agora = now();
                \Log::info('[FINALIZAR] agora=' . $agora . ' | inicioContagem=' . $inicioContagem . ' | tempo_execucao_segundos=' . $atendimento->tempo_execucao_segundos);
                if (!$inicioContagem) {
                    $tempoDecorrido = 0;
                } else {
                    $tempoDecorrido = max(0, $agora->timestamp - $inicioContagem->timestamp);
                }
                $tempoExecucao = max(0, ($atendimento->tempo_execucao_segundos ?? 0) + $tempoDecorrido);
                if ($tempoExecucao < 0) {
                    \Log::warning('tempo_execucao_segundos negativo detectado e corrigido', [
                        'atendimento_id' => $atendimento->id,
                        'valor_calculado' => $tempoExecucao,
                        'incremento' => $tempoDecorrido,
                    ]);
                    $tempoExecucao = 0;
                }
                $atendimento->tempo_execucao_segundos = $tempoExecucao;
            }

            // Atualizar atendimento
            $atendimento->update([
                'status_atual' => 'finalizacao',
                'finalizado_em' => now(),
                'finalizado_por_user_id' => Auth::id(),
                'em_execucao' => false,
                'em_pausa' => false,
                'tempo_execucao_segundos' => $atendimento->tempo_execucao_segundos,
            ]);

            // Criar andamento final com fotos
            $andamento = $atendimento->andamentos()->create([
                'user_id' => Auth::id(),
                'descricao' => $request->observacao ?? 'Atendimento finalizado pelo técnico',
            ]);

            // Salvar as 3 fotos finais
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('atendimentos/fotos', 'public');
                $andamento->fotos()->create([
                    'arquivo' => $path,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('portal-funcionario.chamados')
                ->with('success', '✅ Atendimento finalizado! Aguardando aprovação do gerente para conclusão.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao finalizar atendimento: ' . $e->getMessage());
        }
    }

    /**
     * Agenda Técnica - Estilo Calendar
     */
    public function agenda()
    {
        $funcionarioId = Auth::user()->funcionario_id;

        // Buscar atendimentos para o mês atual
        $atendimentos = Atendimento::with(['cliente', 'empresa'])
            ->where('funcionario_id', $funcionarioId)
            ->whereMonth('data_atendimento', now()->month)
            ->whereYear('data_atendimento', now()->year)
            ->get()
            ->map(function ($at) {
                return [
                    'id' => $at->id,
                    'title' => $at->cliente?->nome_fantasia ?? $at->nome_solicitante ?? 'Sem cliente',
                    'start' => $at->data_atendimento->format('Y-m-d'),
                    'backgroundColor' => match ($at->prioridade) {
                        'alta' => '#ef4444',
                        'media' => '#f59e0b',
                        'baixa' => '#3b82f6',
                        default => '#6b7280',
                    },
                    'url' => route('portal-funcionario.atendimento.show', $at),
                ];
            });

        return view('portal-funcionario.agenda', compact('atendimentos'));
    }

    /**
     * Documentos - Placeholder
     */
    public function documentos()
    {
        return view('portal-funcionario.documentos');
    }
}
