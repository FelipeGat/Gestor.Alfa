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
            ->limit(15)
            ->get();

        $proximoEvento = $this->proximoEventoPonto($registroHoje);

        return view('portal-funcionario.ponto', [
            'registroHoje' => $registroHoje,
            'historico' => $historico,
            'proximoEvento' => $proximoEvento,
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
        ], [
            'tipo.required' => 'Informe o tipo de registro.',
            'tipo.in' => 'Tipo de registro inválido.',
        ]);

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

            $registro->registrado_por_user_id = $userId;
            $registro->observacao = $this->appendObservacao($registro->observacao, 'Registro manual de ponto: ' . $this->rotuloEvento($validated['tipo']) . '.');
            $registro->save();

            DB::commit();

            return redirect()
                ->route('portal-funcionario.ponto')
                ->with('success', $this->rotuloEvento($validated['tipo']) . ' registrado(a) com sucesso.');
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
}
