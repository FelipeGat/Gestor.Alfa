<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\AtendimentoPausa;
use App\Models\AtendimentoStatusHistorico;
use App\Models\RegistroPontoPortal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PortalFuncionarioController extends Controller
{
    private const SEGUNDOS_META_SEMANAL = 158400;

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

        $pontoStatus = [
            'disponivel' => Schema::hasTable('registro_pontos_portal'),
            'concluido' => false,
            'label' => 'Pendente hoje',
            'detalhe' => 'Próximo: Entrada',
        ];

        if ($pontoStatus['disponivel']) {
            $registroHoje = RegistroPontoPortal::query()
                ->where('funcionario_id', $funcionarioId)
                ->whereDate('data_referencia', now()->toDateString())
                ->first();

            $concluido = $registroHoje
                && $registroHoje->entrada_em
                && $registroHoje->intervalo_inicio_em
                && $registroHoje->intervalo_fim_em
                && $registroHoje->saida_em;

            $proximoEvento = $this->proximoEventoPonto($registroHoje);

            $pontoStatus = [
                'disponivel' => true,
                'concluido' => (bool) $concluido,
                'label' => $concluido ? 'Concluído hoje' : 'Pendente hoje',
                'detalhe' => $concluido
                    ? 'Jornada registrada no dia.'
                    : 'Próximo: ' . $this->rotuloEvento($proximoEvento),
            ];
        }

        return view('portal-funcionario.index', compact('totalAbertos', 'totalFinalizados', 'totalEmAtendimento', 'temPausado', 'pontoStatus'));
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
            'orcamento.preCliente',
            'andamentos.fotos',
            'pausas.user',
            'pausas.retomadoPor',
            'iniciadoPor',
            'finalizadoPor'
        ])->findOrFail($atendimento->id);

        $dataReferencia = $atendimento->data_inicio_agendamento?->toDateString();
        $ehHoje = $dataReferencia && $dataReferencia === now()->toDateString();
        $orcamentoVinculado = $atendimento->orcamento;
        $ehPreCliente = $orcamentoVinculado && ! $orcamentoVinculado->cliente_id && $orcamentoVinculado->pre_cliente_id;

        $podeIncluirAtendimento =
            $atendimento->is_orcamento
            && $ehHoje
            && ! $ehPreCliente
            && ! Atendimento::where('atendimento_origem_id', $atendimento->id)->exists();

        return view('portal-funcionario.atendimento-detalhes', compact('atendimento', 'podeIncluirAtendimento', 'ehPreCliente', 'ehHoje'));
    }

    public function incluirAtendimento(Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);

        if (! $atendimento->is_orcamento) {
            return back()->with('error', 'Este registro já é um atendimento do sistema.');
        }

        $dataAgendada = $atendimento->data_inicio_agendamento?->toDateString();

        if (! $dataAgendada) {
            return back()->with('error', 'Este orçamento não possui data de agendamento válida para inclusão de atendimento.');
        }

        if ($dataAgendada !== now()->toDateString()) {
            return back()->with('error', 'O atendimento só pode ser incluído na data agendada (' . Carbon::parse($dataAgendada)->format('d/m/Y') . ').');
        }

        $orcamento = $atendimento->orcamento;

        if ($orcamento && ! $orcamento->cliente_id && $orcamento->pre_cliente_id) {
            return back()->with('error', 'Esta demanda está vinculada a um pré-cliente. Entre em contato com o comercial para converter o pré-cliente em cliente antes de incluir o atendimento.');
        }

        $atendimentoJaGerado = Atendimento::where('atendimento_origem_id', $atendimento->id)->first();
        if ($atendimentoJaGerado) {
            return redirect()
                ->route('portal-funcionario.atendimento.show', $atendimentoJaGerado)
                ->with('success', 'Atendimento já incluído para esta demanda.');
        }

        DB::beginTransaction();
        try {
            $clienteId = $orcamento?->cliente_id ?? $atendimento->cliente_id;

            if (! $clienteId) {
                DB::rollBack();
                return back()->with('error', 'Não foi possível incluir o atendimento: cliente não identificado para esta demanda.');
            }

            $atendimento->update([
                'cliente_id' => $clienteId,
                'nome_solicitante' => $atendimento->nome_solicitante ?: ($orcamento?->nome_cliente ?? null),
                'status_atual' => 'em_atendimento',
                'is_orcamento' => false,
                'data_atendimento' => $atendimento->data_inicio_agendamento ?? now(),
            ]);

            AtendimentoStatusHistorico::create([
                'atendimento_id' => $atendimento->id,
                'status' => 'em_atendimento',
                'observacao' => 'Atendimento incluído pelo técnico a partir de agendamento de orçamento.',
                'user_id' => Auth::id(),
            ]);

            if ($orcamento) {
                $orcamento->update([
                    'atendimento_id' => $atendimento->id,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('portal-funcionario.atendimento.show', $atendimento)
                ->with('success', 'Atendimento incluído com sucesso para esta demanda.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao incluir atendimento: ' . $e->getMessage());
        }
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

        // Validar 1 foto obrigatória
        $request->validate([
            'fotos' => 'required|array|min:1|max:1',
            'fotos.*' => 'required|image|max:5120', // 5MB max
        ], [
            'fotos.required' => 'É obrigatório enviar 1 foto para iniciar o atendimento',
            'fotos.min' => 'É obrigatório enviar exatamente 1 foto',
            'fotos.max' => 'É obrigatório enviar exatamente 1 foto',
        ]);

        DB::beginTransaction();
        try {
            $momentoInicio = now();

            // Atualizar atendimento
            $atendimento->update([
                'status_atual' => 'em_atendimento',
                'iniciado_em' => $momentoInicio,
                'iniciado_por_user_id' => Auth::id(),
                'em_execucao' => true,
                'em_pausa' => false,
            ]);

            $this->registrarEntradaPortal($funcionarioId, (int) Auth::id(), $momentoInicio, $atendimento->id);

            // Criar andamento com fotos
            $andamento = $atendimento->andamentos()->create([
                'user_id' => Auth::id(),
                'descricao' => 'Atendimento iniciado pelo técnico',
            ]);

            // Salvar a foto (padronizado para storage/app/public/atendimentos/fotos)
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('atendimentos/fotos', 'public');
                // Salva apenas o caminho relativo (sem 'public/' ou 'storage/')
                $relativePath = ltrim(str_replace(['public/', 'storage/'], '', $path), '/');
                $andamento->fotos()->create([
                    'arquivo' => $relativePath,
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
            \Illuminate\Support\Facades\Log::info('[PAUSAR] agora=' . $agora . ' | inicioContagem=' . $inicioContagem . ' | tempo_execucao_segundos=' . $atendimento->tempo_execucao_segundos);
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
            \Illuminate\Support\Facades\Log::info('[PAUSAR] tempoDecorrido=' . $tempoDecorrido . ' | novoTempoExecucao=' . $novoTempoExecucao);

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

            if ($request->tipo_pausa === 'almoco') {
                $this->registrarInicioIntervaloPortal($funcionarioId, (int) Auth::id(), $agora, $atendimento->id);
            }

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
            $agora = now();

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
                'iniciado_em' => $agora, // Sempre que retoma, novo início de execução
                'em_execucao' => true,
                'em_pausa' => false,
            ]);

            if ($pausaAtiva->tipo_pausa === 'almoco') {
                $this->registrarFimIntervaloPortal($funcionarioId, (int) Auth::id(), $agora, $atendimento->id);
            }

            $atendimento->refresh();
            DB::commit();
            return back()->with('success', 'Atendimento retomado. Cronômetro de execução reiniciado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao retomar atendimento: ' . $e->getMessage());
        }
    }

    /**
     * Finalizar atendimento - Exige foto final, observação e assinatura do cliente
     */
    public function finalizarAtendimento(Request $request, Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        abort_if($atendimento->funcionario_id !== $funcionarioId, 403);
        abort_if($atendimento->status_atual !== 'em_atendimento', 400, 'Atendimento não está em execução');

        $request->validate([
            'fotos' => 'required|array|min:1|max:1',
            'fotos.*' => 'required|image|max:5120',
            'observacao' => 'required|string|min:5|max:1000',
            'assinatura_cliente_nome' => 'required|string|min:2|max:120',
            'assinatura_cliente_cargo' => 'required|string|min:2|max:120',
            'assinatura_cliente' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(png|jpeg);base64,/', $value)) {
                        $fail('Formato de assinatura inválido. Assine novamente.');
                    }
                },
            ],
        ], [
            'fotos.required' => 'É obrigatório enviar 1 foto para finalizar o atendimento',
            'fotos.min' => 'É obrigatório enviar exatamente 1 foto',
            'fotos.max' => 'É obrigatório enviar exatamente 1 foto',
            'observacao.required' => 'As observações finais são obrigatórias',
            'observacao.min' => 'As observações finais devem ter pelo menos 5 caracteres',
            'assinatura_cliente_nome.required' => 'O nome de quem assina é obrigatório',
            'assinatura_cliente_nome.min' => 'O nome de quem assina deve ter pelo menos 2 caracteres',
            'assinatura_cliente_cargo.required' => 'O cargo de quem assina é obrigatório',
            'assinatura_cliente_cargo.min' => 'O cargo de quem assina deve ter pelo menos 2 caracteres',
            'assinatura_cliente.required' => 'A assinatura do cliente é obrigatória para finalizar',
        ]);

        DB::beginTransaction();
        try {
            $momentoFinalizacao = now();

            // Se estava em execução, calcular tempo final usando timestamps
            if ($atendimento->em_execucao) {
                $ultimaPausa = $atendimento->pausas()->whereNotNull('encerrada_em')->latest('encerrada_em')->first();
                $inicioContagem = $ultimaPausa ? $ultimaPausa->encerrada_em : $atendimento->iniciado_em;
                $agora = now();
                \Illuminate\Support\Facades\Log::info('[FINALIZAR] agora=' . $agora . ' | inicioContagem=' . $inicioContagem . ' | tempo_execucao_segundos=' . $atendimento->tempo_execucao_segundos);
                if (!$inicioContagem) {
                    $tempoDecorrido = 0;
                } else {
                    $tempoDecorrido = max(0, $agora->timestamp - $inicioContagem->timestamp);
                }
                $tempoExecucao = max(0, ($atendimento->tempo_execucao_segundos ?? 0) + $tempoDecorrido);
                if ($tempoExecucao < 0) {
                    \Illuminate\Support\Facades\Log::warning('tempo_execucao_segundos negativo detectado e corrigido', [
                        'atendimento_id' => $atendimento->id,
                        'valor_calculado' => $tempoExecucao,
                        'incremento' => $tempoDecorrido,
                    ]);
                    $tempoExecucao = 0;
                }
                $atendimento->tempo_execucao_segundos = $tempoExecucao;
            }

            $assinaturaData = $request->input('assinatura_cliente');
            if (!preg_match('/^data:image\/(\w+);base64,/', $assinaturaData, $matches)) {
                throw new \RuntimeException('Assinatura inválida.');
            }

            $extensao = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $conteudo = base64_decode(substr($assinaturaData, strpos($assinaturaData, ',') + 1), true);
            if ($conteudo === false) {
                throw new \RuntimeException('Não foi possível processar a assinatura.');
            }

            $assinaturaRelativePath = 'atendimentos/assinaturas/' . uniqid('assinatura_', true) . '.' . $extensao;
            Storage::disk('public')->put($assinaturaRelativePath, $conteudo);

            // Atualizar atendimento
            $atendimento->update([
                'status_atual' => 'finalizacao',
                'finalizado_em' => $momentoFinalizacao,
                'finalizado_por_user_id' => Auth::id(),
                'em_execucao' => false,
                'em_pausa' => false,
                'tempo_execucao_segundos' => $atendimento->tempo_execucao_segundos,
                'assinatura_cliente_nome' => trim($request->assinatura_cliente_nome),
                'assinatura_cliente_cargo' => trim($request->assinatura_cliente_cargo),
                'assinatura_cliente_path' => $assinaturaRelativePath,
            ]);

            $this->registrarSaidaPortal($funcionarioId, (int) Auth::id(), $momentoFinalizacao, $atendimento->id);

            // Criar andamento final com fotos
            $andamento = $atendimento->andamentos()->create([
                'user_id' => Auth::id(),
                'descricao' => $request->observacao ?? 'Atendimento finalizado pelo técnico',
            ]);

            // Salvar a foto final
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('atendimentos/fotos', 'public');
                $relativePath = ltrim(str_replace(['public/', 'storage/'], '', $path), '/');
                $andamento->fotos()->create([
                    'arquivo' => $relativePath,
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
    public function agenda(Request $request)
    {
        $funcionarioId = Auth::user()->funcionario_id;
        $visao = $request->input('visao', 'mes');
        $dataBase = $request->filled('data')
            ? Carbon::createFromFormat('Y-m-d', $request->input('data'))
            : now();

        // Janela ampla para o frontend navegar entre meses sem perder eventos
        $janelaInicio = $dataBase->copy()->startOfYear();
        $janelaFim = $dataBase->copy()->endOfYear();

        // Buscar atendimentos para a visão atual
        $atendimentos = Atendimento::with([
            'cliente:id,nome,nome_fantasia,logradouro,numero,bairro,cidade,estado,complemento,cep',
            'empresa:id,nome_fantasia',
            'assunto:id,nome',
        ])
            ->where('funcionario_id', $funcionarioId)
            ->where(function ($q) use ($janelaInicio, $janelaFim) {
                $q->whereBetween('data_inicio_agendamento', [$janelaInicio, $janelaFim])
                    ->orWhereBetween('data_atendimento', [$janelaInicio, $janelaFim]);
            })
            ->orderByRaw('COALESCE(data_inicio_agendamento, data_atendimento) asc')
            ->get()
            ->map(function ($at) {
                $inicio = $at->data_inicio_agendamento ?? $at->data_atendimento;
                $fim = $at->data_fim_agendamento;

                return [
                    'id' => $at->id,
                    'numero_atendimento' => $at->numero_atendimento,
                    'cliente_nome' => $at->cliente?->nome_fantasia ?? $at->cliente?->nome ?? $at->nome_solicitante ?? 'Sem cliente',
                    'empresa_nome' => $at->empresa?->nome_fantasia ?? '—',
                    'assunto_nome' => $at->assunto?->nome ?? 'Sem assunto',
                    'descricao' => (string) ($at->descricao ?? ''),
                    'tipo_demanda' => $at->is_orcamento ? 'orcamento' : 'atendimento',
                    'prioridade' => $at->prioridade,
                    'status' => $at->status_atual,
                    'telefone_solicitante' => $at->telefone_solicitante,
                    'data_atendimento' => optional($inicio)->format('Y-m-d'),
                    'inicio' => optional($inicio)->format('H:i'),
                    'fim' => optional($fim)->format('H:i'),
                    'duracao_minutos' => $at->duracao_agendamento_minutos,
                    'endereco' => trim(collect([
                        $at->cliente?->logradouro,
                        $at->cliente?->numero,
                        $at->cliente?->bairro,
                        $at->cliente?->cidade,
                        $at->cliente?->estado,
                    ])->filter()->implode(', ')),
                    'cep' => $at->cliente?->cep,
                    'complemento' => $at->cliente?->complemento,
                    'backgroundColor' => match ($at->prioridade) {
                        'alta' => '#ef4444',
                        'media' => '#f59e0b',
                        'baixa' => '#3b82f6',
                        default => '#6b7280',
                    },
                    'url' => route('portal-funcionario.atendimento.show', $at),
                ];
            });

        return view('portal-funcionario.agenda', compact('atendimentos', 'visao', 'dataBase'));
    }

    public function ponto()
    {
        $funcionarioId = (int) Auth::user()->funcionario_id;

        if (!Schema::hasTable('registro_pontos_portal')) {
            return redirect()
                ->route('portal-funcionario.index')
                ->with('error', 'Módulo de registro de ponto ainda não está disponível neste ambiente.');
        }

        $hoje = now()->toDateString();
        $registroHoje = RegistroPontoPortal::query()
            ->where('funcionario_id', $funcionarioId)
            ->whereDate('data_referencia', $hoje)
            ->first();

        $historico = RegistroPontoPortal::query()
            ->where('funcionario_id', $funcionarioId)
            ->orderByDesc('data_referencia')
            ->paginate(10);

        $feriadosNacionais = [];
        if ($historico->total() > 0) {
            $historicoCollection = $historico->getCollection();
            $inicioHistorico = Carbon::parse($historicoCollection->min('data_referencia'))->startOfDay();
            $fimHistorico = Carbon::parse($historicoCollection->max('data_referencia'))->endOfDay();
            $feriadosNacionais = $this->mapaFeriadosNacionaisPortal($inicioHistorico, $fimHistorico);
        }

        $historico->getCollection()->transform(function (RegistroPontoPortal $registro) use ($feriadosNacionais) {
            $dia = Carbon::parse($registro->data_referencia)->startOfDay();
            $dataChave = $dia->toDateString();
            $ehDomingo = $dia->isSunday();
            $ehFeriado = array_key_exists($dataChave, $feriadosNacionais);
            $segundosTrabalhados = $this->calcularSegundosTrabalhadosPortal($registro);

            $registro->eh_domingo = $ehDomingo;
            $registro->eh_feriado = $ehFeriado;
            $registro->feriado_nome = $feriadosNacionais[$dataChave] ?? null;
            $registro->total_formatado = $this->formatarSegundosPortal($segundosTrabalhados);
            $registro->status = $this->resolverStatusPortal($registro, $segundosTrabalhados, $ehDomingo, $ehFeriado);
            $registro->ponto_fora_atendimento = (bool) $registro->registrado_fora_atendimento;

            return $registro;
        });

        $mesesDaPagina = collect($historico->items())
            ->map(fn (RegistroPontoPortal $registro) => Carbon::parse($registro->data_referencia)->format('m/Y'))
            ->unique()
            ->values();

        $saldoBancoHorasMes = $this->calcularSaldoBancoHorasMesesPortal($funcionarioId, $mesesDaPagina);

        $proximoEvento = $this->proximoEventoPonto($registroHoje);
        $ultimoRegistroHoje = $registroHoje ? $this->ultimoRegistroPontoEm($registroHoje) : null;
        $bloqueioMinutosRestantes = 0;
        if ($ultimoRegistroHoje && $ultimoRegistroHoje->diffInMinutes(now()) < 15) {
            $bloqueioMinutosRestantes = max(1, 15 - $ultimoRegistroHoje->diffInMinutes(now()));
        }
        $atendimentoHoje = $this->atendimentoDoDiaPortal($funcionarioId, now());

        $enderecoAtendimento = null;
        if ($atendimentoHoje?->cliente) {
            $enderecoAtendimento = collect([
                $atendimentoHoje->cliente->logradouro,
                $atendimentoHoje->cliente->numero,
                $atendimentoHoje->cliente->bairro,
                $atendimentoHoje->cliente->cidade,
                $atendimentoHoje->cliente->estado,
            ])->filter()->implode(', ');
        }

        return view('portal-funcionario.ponto', [
            'registroHoje' => $registroHoje,
            'historico' => $historico,
            'saldoBancoHorasMes' => $saldoBancoHorasMes,
            'proximoEvento' => $proximoEvento,
            'bloqueioMinutosRestantes' => $bloqueioMinutosRestantes,
            'atendimentoHoje' => $atendimentoHoje,
            'enderecoAtendimento' => $enderecoAtendimento,
            'eventos' => [
                'entrada' => 'Entrada',
                'saida_almoco' => 'Saída almoço',
                'retorno_almoco' => 'Retorno almoço',
                'saida' => 'Saída',
            ],
        ]);
    }

    public function registrarPonto(Request $request)
    {
        $funcionarioId = (int) Auth::user()->funcionario_id;
        $userId = (int) Auth::id();

        if (!Schema::hasTable('registro_pontos_portal')) {
            return back()->with('error', 'Tabela de registro de ponto não encontrada neste ambiente.');
        }

        $validated = $request->validate([
            'tipo' => 'required|in:entrada,saida_almoco,retorno_almoco,saida',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'fora_atendimento' => 'nullable|boolean',
            'fora_atendimento_confirmado' => 'nullable|boolean',
            'distancia_atendimento_metros' => 'nullable|integer|min:0|max:200000',
            'justificativa_fora_atendimento' => 'nullable|string|max:1000',
        ], [
            'tipo.required' => 'Informe o tipo de registro.',
            'tipo.in' => 'Tipo de registro inválido.',
            'latitude.required' => 'Não foi possível obter sua localização. Ative o GPS e tente novamente.',
            'longitude.required' => 'Não foi possível obter sua localização. Ative o GPS e tente novamente.',
        ]);

        $atendimentoHoje = $this->atendimentoDoDiaPortal($funcionarioId, now());
        $foraAtendimento = (bool) ($validated['fora_atendimento'] ?? false);
        $foraAtendimentoConfirmado = (bool) ($validated['fora_atendimento_confirmado'] ?? false);

        if ($atendimentoHoje && $foraAtendimento && !$foraAtendimentoConfirmado) {
            return back()->with('error', 'Registro fora do atendimento requer confirmação explícita do funcionário.');
        }

        if (in_array($validated['tipo'], ['entrada', 'saida'], true) && !$request->hasFile('foto')) {
            return back()->with('error', 'Para entrada e saída é obrigatório enviar uma foto.');
        }

        $momento = now();

        DB::beginTransaction();
        try {
            $registro = RegistroPontoPortal::query()->firstOrCreate(
                [
                    'funcionario_id' => $funcionarioId,
                    'data_referencia' => $momento->toDateString(),
                ],
                [
                    'registrado_por_user_id' => $userId,
                ]
            );

            $proximoEvento = $this->proximoEventoPonto($registro);
            if ($validated['tipo'] !== $proximoEvento) {
                DB::rollBack();
                return back()->with('error', 'Sequência inválida. O próximo registro esperado é: ' . $this->rotuloEvento($proximoEvento) . '.');
            }

            $ultimoRegistroEm = $this->ultimoRegistroPontoEm($registro);
            if ($ultimoRegistroEm && $ultimoRegistroEm->diffInMinutes($momento) < 15) {
                DB::rollBack();
                $restante = 15 - $ultimoRegistroEm->diffInMinutes($momento);
                return back()->with('error', 'Aguarde ' . max(1, $restante) . ' minuto(s) para registrar o próximo evento.');
            }

            $this->preencherGeoDoEvento($registro, $validated['tipo'], (float) $validated['latitude'], (float) $validated['longitude']);

            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('ponto/' . $funcionarioId . '/' . now()->format('Y/m'), 'public');
                if ($validated['tipo'] === 'entrada') {
                    $registro->entrada_foto_path = $fotoPath;
                }
                if ($validated['tipo'] === 'saida') {
                    $registro->saida_foto_path = $fotoPath;
                }
            }

            switch ($validated['tipo']) {
                case 'entrada':
                    $registro->entrada_em = $momento;
                    break;
                case 'saida_almoco':
                    $registro->intervalo_inicio_em = $momento;
                    break;
                case 'retorno_almoco':
                    $registro->intervalo_fim_em = $momento;
                    break;
                case 'saida':
                    $registro->saida_em = $momento;
                    break;
            }

            if ($atendimentoHoje && $foraAtendimento) {
                $registro->registrado_fora_atendimento = true;
                $registro->distancia_atendimento_metros = $validated['distancia_atendimento_metros'] ?? $registro->distancia_atendimento_metros;
                $registro->justificativa_fora_atendimento = $validated['justificativa_fora_atendimento']
                    ?? 'Registro confirmado fora do endereço de atendimento agendado.';
            }

            $registro->registrado_por_user_id = $userId;
            $registro->observacao = $this->appendObservacao($registro->observacao, 'Registro manual de ponto: ' . $this->rotuloEvento($validated['tipo']) . '.');
            if ($atendimentoHoje && $foraAtendimento) {
                $registro->observacao = $this->appendObservacao($registro->observacao, 'ATENÇÃO: ponto registrado fora do endereço do atendimento agendado e sujeito a auditoria.');
            }
            $registro->save();

            DB::commit();

            $mensagem = $this->rotuloEvento($validated['tipo']) . ' registrado(a) com sucesso.';
            if (in_array($validated['tipo'], ['entrada', 'saida'], true)) {
                $mensagem = $validated['tipo'] === 'entrada'
                    ? 'Seu registro de ponto foi feito com sucesso. Bom trabalho.'
                    : 'Seu registro de saída foi feito com sucesso. Bom descanso.';
            }

            return redirect()
                ->route('portal-funcionario.ponto')
                ->with('success', $mensagem);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao registrar ponto: ' . $e->getMessage());
        }
    }

    /**
     * Documentos - Placeholder
     */
    public function documentos()
    {
        return view('portal-funcionario.documentos');
    }

    private function proximoEventoPonto(?RegistroPontoPortal $registro): string
    {
        if (!$registro || !$registro->entrada_em) {
            return 'entrada';
        }

        if (!$registro->intervalo_inicio_em) {
            return 'saida_almoco';
        }

        if (!$registro->intervalo_fim_em) {
            return 'retorno_almoco';
        }

        if (!$registro->saida_em) {
            return 'saida';
        }

        return 'saida';
    }

    private function rotuloEvento(string $evento): string
    {
        return match ($evento) {
            'entrada' => 'Entrada',
            'saida_almoco' => 'Saída almoço',
            'retorno_almoco' => 'Retorno almoço',
            'saida' => 'Saída',
            default => 'Registro',
        };
    }

    private function registrarEntradaPortal(int $funcionarioId, int $userId, Carbon $momento, int $atendimentoId): void
    {
        if (!Schema::hasTable('registro_pontos_portal')) {
            return;
        }

        $registro = $this->obterOuCriarRegistroPonto($funcionarioId, $userId, $momento);
        if (!$registro->entrada_em) {
            $registro->entrada_em = $momento;
        }

        $registro->registrado_por_user_id = $userId;
        $registro->observacao = $this->appendObservacao($registro->observacao, 'Entrada registrada via atendimento #' . $atendimentoId . '.');
        $registro->save();
    }

    private function registrarInicioIntervaloPortal(int $funcionarioId, int $userId, Carbon $momento, int $atendimentoId): void
    {
        if (!Schema::hasTable('registro_pontos_portal')) {
            return;
        }

        $registro = $this->obterOuCriarRegistroPonto($funcionarioId, $userId, $momento);
        if (!$registro->intervalo_inicio_em || ($registro->intervalo_fim_em && $registro->intervalo_inicio_em->gt($registro->intervalo_fim_em))) {
            $registro->intervalo_inicio_em = $momento;
            $registro->intervalo_fim_em = null;
        }

        $registro->registrado_por_user_id = $userId;
        $registro->observacao = $this->appendObservacao($registro->observacao, 'Início de intervalo (almoço) via atendimento #' . $atendimentoId . '.');
        $registro->save();
    }

    private function registrarFimIntervaloPortal(int $funcionarioId, int $userId, Carbon $momento, int $atendimentoId): void
    {
        if (!Schema::hasTable('registro_pontos_portal')) {
            return;
        }

        $registro = $this->obterOuCriarRegistroPonto($funcionarioId, $userId, $momento);
        if ($registro->intervalo_inicio_em && !$registro->intervalo_fim_em) {
            $registro->intervalo_fim_em = $momento;
        }

        $registro->registrado_por_user_id = $userId;
        $registro->observacao = $this->appendObservacao($registro->observacao, 'Fim de intervalo (almoço) via atendimento #' . $atendimentoId . '.');
        $registro->save();
    }

    private function registrarSaidaPortal(int $funcionarioId, int $userId, Carbon $momento, int $atendimentoId): void
    {
        if (!Schema::hasTable('registro_pontos_portal')) {
            return;
        }

        $registro = $this->obterOuCriarRegistroPonto($funcionarioId, $userId, $momento);
        if (!$registro->saida_em || Carbon::parse($registro->saida_em)->lt($momento)) {
            $registro->saida_em = $momento;
        }

        if (!$registro->entrada_em) {
            $registro->entrada_em = $momento;
        }

        $registro->registrado_por_user_id = $userId;
        $registro->observacao = $this->appendObservacao($registro->observacao, 'Saída registrada via finalização do atendimento #' . $atendimentoId . '.');
        $registro->save();
    }

    private function obterOuCriarRegistroPonto(int $funcionarioId, int $userId, Carbon $momento): RegistroPontoPortal
    {
        return RegistroPontoPortal::query()->firstOrCreate(
            [
                'funcionario_id' => $funcionarioId,
                'data_referencia' => $momento->toDateString(),
            ],
            [
                'registrado_por_user_id' => $userId,
                'observacao' => null,
            ]
        );
    }

    private function appendObservacao(?string $textoAtual, string $novoTrecho): string
    {
        $prefixo = '[' . now()->format('d/m/Y H:i') . '] ';
        $novo = $prefixo . $novoTrecho;

        if (!$textoAtual) {
            return $novo;
        }

        return trim($textoAtual) . PHP_EOL . $novo;
    }

    private function resolverStatusPortal(RegistroPontoPortal $registro, int $segundosTrabalhados, bool $ehDomingo, bool $ehFeriado): string
    {
        $possuiBatidas = $this->possuiBatidasPortal($registro);
        if (!$possuiBatidas) {
            return ($ehDomingo || $ehFeriado) ? '' : 'Falta';
        }

        $intervaloIncompleto = ($registro->intervalo_inicio_em && !$registro->intervalo_fim_em)
            || (!$registro->intervalo_inicio_em && $registro->intervalo_fim_em);

        if (!$registro->entrada_em || !$registro->saida_em || $intervaloIncompleto) {
            return 'Incompleto';
        }

        if ($ehDomingo || $ehFeriado) {
            return $ehFeriado ? 'Extra feriado' : 'Extra';
        }

        return $segundosTrabalhados > 29400 ? 'Extra' : 'Normal';
    }

    private function possuiBatidasPortal(RegistroPontoPortal $registro): bool
    {
        return (bool) ($registro->entrada_em || $registro->intervalo_inicio_em || $registro->intervalo_fim_em || $registro->saida_em);
    }

    private function calcularSegundosTrabalhadosPortal(RegistroPontoPortal $registro): int
    {
        if (!$registro->entrada_em || !$registro->saida_em) {
            return 0;
        }

        $entradaTimestamp = strtotime((string) $registro->entrada_em);
        $saidaTimestamp = strtotime((string) $registro->saida_em);
        if (!$entradaTimestamp || !$saidaTimestamp || $saidaTimestamp <= $entradaTimestamp) {
            return 0;
        }

        $segundos = $saidaTimestamp - $entradaTimestamp;

        if ($registro->intervalo_inicio_em && $registro->intervalo_fim_em) {
            $inicioIntervaloTimestamp = strtotime((string) $registro->intervalo_inicio_em);
            $fimIntervaloTimestamp = strtotime((string) $registro->intervalo_fim_em);

            if ($inicioIntervaloTimestamp && $fimIntervaloTimestamp && $fimIntervaloTimestamp > $inicioIntervaloTimestamp) {
                $segundos -= ($fimIntervaloTimestamp - $inicioIntervaloTimestamp);
            }
        }

        return max(0, $segundos);
    }

    private function formatarSegundosPortal(int $segundos): string
    {
        $segundos = max(0, $segundos);
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);

        return sprintf('%02d:%02d', $horas, $minutos);
    }

    private function mapaFeriadosNacionaisPortal(Carbon $inicio, Carbon $fim): array
    {
        $mapa = [];
        $anoInicio = (int) $inicio->year;
        $anoFim = (int) $fim->year;

        for ($ano = $anoInicio; $ano <= $anoFim; $ano++) {
            $fixos = [
                sprintf('%04d-01-01', $ano) => 'Confraternização Universal',
                sprintf('%04d-04-21', $ano) => 'Tiradentes',
                sprintf('%04d-05-01', $ano) => 'Dia do Trabalho',
                sprintf('%04d-09-07', $ano) => 'Independência do Brasil',
                sprintf('%04d-10-12', $ano) => 'Nossa Senhora Aparecida',
                sprintf('%04d-11-02', $ano) => 'Finados',
                sprintf('%04d-11-15', $ano) => 'Proclamação da República',
                sprintf('%04d-11-20', $ano) => 'Dia da Consciência Negra',
                sprintf('%04d-12-25', $ano) => 'Natal',
            ];

            $pascoa = Carbon::createFromTimestamp(easter_date($ano))->startOfDay();
            $moveis = [
                $pascoa->copy()->subDays(48)->toDateString() => 'Carnaval',
                $pascoa->copy()->subDays(47)->toDateString() => 'Carnaval',
                $pascoa->copy()->subDays(2)->toDateString() => 'Sexta-feira Santa',
                $pascoa->copy()->toDateString() => 'Páscoa',
                $pascoa->copy()->addDays(60)->toDateString() => 'Corpus Christi',
            ];

            foreach ($fixos + $moveis as $data => $nome) {
                if ($data >= $inicio->toDateString() && $data <= $fim->toDateString()) {
                    $mapa[$data] = $nome;
                }
            }
        }

        return $mapa;
    }

    private function calcularSaldoBancoHorasMesesPortal(int $funcionarioId, $mesesReferencia): array
    {
        $saldoPorMes = [];

        foreach ($mesesReferencia as $mesAno) {
            [$mes, $ano] = explode('/', $mesAno);
            $inicioMes = Carbon::createFromDate((int) $ano, (int) $mes, 1)->startOfMonth();
            $fimMes = $inicioMes->copy()->endOfMonth();

            $registrosMes = RegistroPontoPortal::query()
                ->where('funcionario_id', $funcionarioId)
                ->whereBetween('data_referencia', [$inicioMes->toDateString(), $fimMes->toDateString()])
                ->orderBy('data_referencia')
                ->get();

            $totaisSemanais = [];
            foreach ($registrosMes as $registro) {
                $dia = Carbon::parse($registro->data_referencia)->startOfDay();
                $chaveSemana = sprintf('%s-W%s', $dia->format('o'), $dia->format('W'));
                $totaisSemanais[$chaveSemana] = ($totaisSemanais[$chaveSemana] ?? 0) + $this->calcularSegundosTrabalhadosPortal($registro);
            }

            $saldoSegundos = collect($totaisSemanais)
                ->reduce(fn (int $saldo, int $segundosSemana) => $saldo + ($segundosSemana - self::SEGUNDOS_META_SEMANAL), 0);

            $saldoPorMes[$mesAno] = [
                'segundos' => $saldoSegundos,
                'formatado' => $this->formatarSaldoBancoHorasPortal($saldoSegundos),
                'positivo' => $saldoSegundos >= 0,
            ];
        }

        return $saldoPorMes;
    }

    private function formatarSaldoBancoHorasPortal(int $segundos): string
    {
        $sinal = $segundos < 0 ? '-' : '+';
        $segundosAbsolutos = abs($segundos);
        $horas = intdiv($segundosAbsolutos, 3600);
        $minutos = intdiv($segundosAbsolutos % 3600, 60);

        return sprintf('%s%02d:%02d', $sinal, $horas, $minutos);
    }

    private function atendimentoDoDiaPortal(int $funcionarioId, Carbon $momento): ?Atendimento
    {
        return Atendimento::query()
            ->with('cliente')
            ->where('funcionario_id', $funcionarioId)
            ->where(function ($query) use ($momento) {
                $query->whereDate('data_atendimento', $momento->toDateString())
                    ->orWhere(function ($q) use ($momento) {
                        $q->whereNotNull('data_inicio_agendamento')
                            ->whereNotNull('data_fim_agendamento')
                            ->where('data_inicio_agendamento', '<=', $momento)
                            ->where('data_fim_agendamento', '>=', $momento);
                    });
            })
            ->orderByDesc('data_inicio_agendamento')
            ->first();
    }

    private function ultimoRegistroPontoEm(RegistroPontoPortal $registro): ?Carbon
    {
        $eventos = collect([
            $registro->entrada_em,
            $registro->intervalo_inicio_em,
            $registro->intervalo_fim_em,
            $registro->saida_em,
        ])->filter();

        if ($eventos->isEmpty()) {
            return null;
        }

        $ultimo = $eventos->map(fn ($evento) => Carbon::parse($evento))->sort()->last();

        return $ultimo instanceof Carbon ? $ultimo : Carbon::parse($ultimo);
    }

    private function preencherGeoDoEvento(RegistroPontoPortal $registro, string $tipo, float $latitude, float $longitude): void
    {
        if ($tipo === 'entrada') {
            $registro->entrada_latitude = $latitude;
            $registro->entrada_longitude = $longitude;
            return;
        }

        if ($tipo === 'saida_almoco') {
            $registro->intervalo_inicio_latitude = $latitude;
            $registro->intervalo_inicio_longitude = $longitude;
            return;
        }

        if ($tipo === 'retorno_almoco') {
            $registro->intervalo_fim_latitude = $latitude;
            $registro->intervalo_fim_longitude = $longitude;
            return;
        }

        if ($tipo === 'saida') {
            $registro->saida_latitude = $latitude;
            $registro->saida_longitude = $longitude;
        }
    }
}
