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

        return view('portal-funcionario.index', compact('totalAbertos', 'totalFinalizados'));
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
            ->where(function($query) {
                $query->where('status_atual', 'aberto')
                      ->orWhere(function($q) {
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
            ->where('status_atual', 'concluido')
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

        $atendimento->load(['cliente', 'empresa', 'assunto', 'andamentos.fotos', 'pausas.user']);

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
                    'caminho' => $path,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('portal-funcionario.chamados')
                ->with('success', 'Atendimento iniciado com sucesso! Cronômetro em execução.');
                
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

        $request->validate([
            'tipo_pausa' => 'required|in:almoco,deslocamento,material,fim_dia',
            'foto' => 'required|image|max:5120',
        ], [
            'tipo_pausa.required' => 'Selecione o tipo de pausa',
            'foto.required' => 'É obrigatório enviar 1 foto ao pausar',
        ]);

        DB::beginTransaction();
        try {
            // Calcular tempo decorrido desde o início ou última retomada
            $ultimaPausa = $atendimento->pausas()->whereNotNull('encerrada_em')->latest('encerrada_em')->first();
            $inicioContagem = $ultimaPausa ? $ultimaPausa->encerrada_em : $atendimento->iniciado_em;
            $tempoDecorrido = now()->diffInSeconds($inicioContagem);

            // Atualizar tempo de execução
            $atendimento->update([
                'tempo_execucao_segundos' => $atendimento->tempo_execucao_segundos + $tempoDecorrido,
                'em_execucao' => false,
                'em_pausa' => true,
            ]);

            // Salvar foto
            $fotoPath = $request->file('foto')->store('atendimentos/pausas', 'public');

            // Criar registro de pausa
            AtendimentoPausa::create([
                'atendimento_id' => $atendimento->id,
                'user_id' => Auth::id(),
                'tipo_pausa' => $request->tipo_pausa,
                'iniciada_em' => now(),
                'foto_inicio_path' => $fotoPath,
            ]);

            DB::commit();

            return back()->with('success', 'Atendimento pausado. Cronômetro de pausa iniciado.');
            
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

        $request->validate([
            'foto' => 'required|image|max:5120',
        ], [
            'foto.required' => 'É obrigatório enviar 1 foto ao retomar',
        ]);

        DB::beginTransaction();
        try {
            // Buscar pausa ativa
            $pausaAtiva = $atendimento->pausaAtiva();
            
            if (!$pausaAtiva) {
                return back()->with('error', 'Nenhuma pausa ativa encontrada');
            }

            // Salvar foto de retorno
            $fotoPath = $request->file('foto')->store('atendimentos/pausas', 'public');
            $pausaAtiva->foto_retorno_path = $fotoPath;

            // Encerrar pausa e calcular tempo
            $pausaAtiva->encerrar();

            // Atualizar atendimento
            $atendimento->update([
                'tempo_pausa_segundos' => $atendimento->tempo_pausa_segundos + $pausaAtiva->tempo_segundos,
                'em_execucao' => true,
                'em_pausa' => false,
            ]);

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
            // Se estava em execução, calcular tempo final
            if ($atendimento->em_execucao) {
                $ultimaPausa = $atendimento->pausas()->whereNotNull('encerrada_em')->latest('encerrada_em')->first();
                $inicioContagem = $ultimaPausa ? $ultimaPausa->encerrada_em : $atendimento->iniciado_em;
                $tempoDecorrido = now()->diffInSeconds($inicioContagem);
                $atendimento->tempo_execucao_segundos += $tempoDecorrido;
            }

            // Atualizar atendimento
            $atendimento->update([
                'status_atual' => 'concluido',
                'finalizado_em' => now(),
                'em_execucao' => false,
                'em_pausa' => false,
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
                    'caminho' => $path,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('portal-funcionario.chamados')
                ->with('success', 'Atendimento finalizado com sucesso!');
                
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
                    'backgroundColor' => match($at->prioridade) {
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
