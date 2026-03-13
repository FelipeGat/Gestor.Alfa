<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;
use App\Models\AtendimentoStatusHistorico;
use App\Models\User;
use App\Services\AgendaTecnicaService;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Traits\LogsUserActivity;

class AtendimentoController extends Controller
{
    use LogsUserActivity;

    protected $notificacaoService;

    public function __construct(NotificacaoService $notificacaoService)
    {
        $this->notificacaoService = $notificacaoService;
    }

    public function index(Request $request)
    {
        $query = Atendimento::with([
            'cliente',
            'assunto',
            'empresa',
            'funcionario'
        ])
        ->select([
            'id',
            'numero_atendimento',
            'cliente_id',
            'nome_solicitante',
            'telefone_solicitante',
            'assunto_id',
            'prioridade',
            'empresa_id',
            'funcionario_id',
            'status_atual',
            'data_atendimento',
            'data_inicio_agendamento',
            'data_fim_agendamento',
            'periodo_agendamento',
            'created_at'
        ]);

        // 🔎 BUSCA (cliente ou solicitante)
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('nome_solicitante', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($c) use ($search) {
                        $c->where('nome', 'like', "%{$search}%");
                    });
            });
        }

        // 🚨 PRIORIDADE
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        // 📌 STATUS
        if ($request->filled('status')) {
            $query->where('status_atual', $request->status);
        }

        // EMPRESA
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        // TÉCNICO
        if ($request->filled('tecnico_id')) {
            $query->where('funcionario_id', $request->tecnico_id);
        }

        // TRIAGEM PENDENTE (sem empresa ou sem técnico)
        if ($request->boolean('triagem_pendente')) {
            $query->where(function ($q) {
                $q->whereNull('empresa_id')
                    ->orWhereNull('funcionario_id');
            });
        }

        // FILTRO DE PERÍODO PERSONALIZADO
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_atendimento', [
                $request->data_inicio,
                $request->data_fim
            ]);
        } else {
            // 📅 PERÍODO PADRÃO
            $periodo = $request->input('periodo', 'mes');
            match ($periodo) {
                'dia' => $query->whereDate('data_atendimento', today()),
                'semana' => $query->whereBetween('data_atendimento', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]),
                'ano' => $query->whereYear('data_atendimento', now()->year),
                default => $query->whereMonth('data_atendimento', now()->month)
                    ->whereYear('data_atendimento', now()->year),
            };
        }

        $atendimentos = $query
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderByDesc('data_atendimento')
            ->paginate(10)
            ->withQueryString();

        $funcionarios = Funcionario::where('ativo', true)
            ->orderBy('nome')
            ->get();

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('atendimentos.index', compact(
            'atendimentos',
            'funcionarios',
            'empresas'
        ));
    }



    public function create()
    {
        return view('atendimentos.create', [
            'clientes'     => Cliente::orderBy('nome')->get(),
            'assuntos'     => Assunto::where('ativo', true)->orderBy('nome')->get(),
            'empresas'     => Empresa::orderBy('nome_fantasia')->get(),
            'funcionarios' => Funcionario::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function edit(Atendimento $atendimento)
    {
        $atendimento->load([
            'andamentos.user',
            'andamentos.fotos'
        ]);

        return view('atendimentos.edit', [
            'atendimento' => $atendimento,
            'funcionarios' => Funcionario::where('ativo', true)->orderBy('nome')->get(['id', 'nome'])
        ]);
    }

    public function store(Request $request, AgendaTecnicaService $agendaService)
    {
        $request->validate([
            'nome_solicitante' => 'required|string|max:255',
            'assunto_id'       => 'required|exists:assuntos,id',
            'descricao'        => 'required|string',
            'prioridade'       => 'required|in:baixa,media,alta',
            'empresa_id'       => 'required|exists:empresas,id',
            'status_inicial'   => 'required|in:orcamento,aberto,garantia',
            'funcionario_id'   => 'nullable|exists:funcionarios,id',
        ]);

        $agendarTecnico = $request->boolean('agendar_tecnico') || $request->filled('data_agendamento');

        if ($agendarTecnico) {
            $request->validate([
                'funcionario_id' => ['required', 'exists:funcionarios,id'],
                'data_agendamento' => ['required', 'date_format:Y-m-d'],
                'periodo_agendamento' => ['required', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
                'hora_inicio' => ['required', 'date_format:H:i'],
                'duracao_horas' => ['required', 'integer', 'min:1', 'max:4'],
            ]);
        }

        $statusInicial = $request->status_inicial;

        // Gerar número de atendimento de forma segura contra condições de corrida
        $numeroAtendimento = DB::transaction(function () {
            // Bloqueia a linha com "lock for update" para evitar concorrência
            $ultimoRegistro = DB::table('atendimentos')
                ->lockForUpdate()
                ->orderBy('numero_atendimento', 'desc')
                ->first(['numero_atendimento']);

            $ultimoNumero = $ultimoRegistro ? $ultimoRegistro->numero_atendimento : 0;
            return $ultimoNumero + 1;
        });

        $atendimento = Atendimento::create([
            'numero_atendimento'   => $numeroAtendimento,
            'cliente_id'           => $request->cliente_id,
            'nome_solicitante'     => $request->nome_solicitante,
            'telefone_solicitante' => $request->telefone_solicitante,
            'email_solicitante'    => $request->email_solicitante,
            'assunto_id'           => $request->assunto_id,
            'descricao'            => $request->descricao,
            'prioridade'           => $request->prioridade,
            'empresa_id'           => $request->empresa_id,
            'funcionario_id'       => $request->funcionario_id,
            'status_atual'         => $statusInicial,
            'is_orcamento'         => $statusInicial === 'orcamento',
            'data_atendimento'     => now(),
        ]);

        if ($agendarTecnico) {
            $agendaService->agendarAtendimento(
                $atendimento,
                (int) $request->funcionario_id,
                $request->data_agendamento,
                $request->periodo_agendamento,
                $request->hora_inicio,
                (int) $request->duracao_horas
            );
        }

        AtendimentoStatusHistorico::create([
            'atendimento_id' => $atendimento->id,
            'status'         => $statusInicial,
            'observacao'     => 'Abertura do atendimento',
            'user_id'        => Auth::id(),
        ]);

        // Notificar técnico
        if ($atendimento->funcionario_id) {
            $tecnico = Funcionario::with('user')->find($atendimento->funcionario_id);
            if ($tecnico && $tecnico->user) {
                $this->notificacaoService->enviarParaUsuario(
                    $tecnico->user,
                    "Novo Chamado #{$atendimento->numero_atendimento}",
                    "Você recebeu um novo chamado: {$atendimento->assunto->nome}",
                    ['type' => 'novo_chamado', 'id' => $atendimento->id]
                );
            }
        }

        $this->registrarLog('chamado aberto', $atendimento, [
            'numero'     => $atendimento->numero_atendimento,
            'prioridade' => $atendimento->prioridade,
            'status'     => $atendimento->status_atual,
        ]);

        return redirect()
            ->route('atendimentos.index')
            ->with('success', $agendarTecnico
                ? 'Atendimento registrado e técnico agendado com sucesso.'
                : 'Atendimento registrado com sucesso.');
    }

    public function update(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'nome_solicitante' => 'required|string|max:255',
            'assunto_id'       => 'required|exists:assuntos,id',
            'descricao'        => 'required|string',
            'prioridade'       => 'required|in:baixa,media,alta',
            'empresa_id'       => 'required|exists:empresas,id',
        ]);

        $atendimento->update([
            'cliente_id'           => $request->cliente_id,
            'nome_solicitante'     => $request->nome_solicitante,
            'telefone_solicitante' => $request->telefone_solicitante,
            'email_solicitante'    => $request->email_solicitante,
            'assunto_id'           => $request->assunto_id,
            'descricao'            => $request->descricao,
            'prioridade'           => $request->prioridade,
            'empresa_id'           => $request->empresa_id,
            'funcionario_id'       => $request->funcionario_id,
        ]);

        $this->registrarLog('chamado atualizado', $atendimento, [
            'numero'     => $atendimento->numero_atendimento,
            'prioridade' => $atendimento->prioridade,
        ]);

        return redirect()
            ->route('atendimentos.index')
            ->with('success', 'Atendimento atualizado com sucesso.');
    }

    // METODO ATUALIZAR CAMPO
    public function atualizarCampo(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'campo' => 'required|in:status,prioridade,funcionario_id,empresa_id,data_inicio_agendamento',
            'valor' => 'nullable'
        ]);

        if ($request->campo === 'empresa_id' && filled($request->valor)) {
            $request->validate([
                'valor' => 'exists:empresas,id',
            ]);
        }

        if ($request->campo === 'funcionario_id' && filled($request->valor)) {
            $request->validate([
                'valor' => 'exists:funcionarios,id',
            ]);
        }

        if ($request->campo === 'data_inicio_agendamento') {
            if (! filled($request->valor)) {
                $atendimento->update([
                    'data_inicio_agendamento' => null,
                    'data_fim_agendamento' => null,
                ]);

                return response()->json(['success' => true]);
            }

            $request->validate([
                'valor' => 'date_format:Y-m-d',
            ]);

            $dataAgendada = Carbon::createFromFormat('Y-m-d', (string) $request->valor)->startOfDay();
            $atendimento->update([
                'data_inicio_agendamento' => $dataAgendada,
            ]);

            return response()->json(['success' => true]);
        }

        // STATUS → HISTÓRICO
        if ($request->campo === 'status') {

            if ($atendimento->status_atual === 'concluido') {
                return response()->json([
                    'success' => false,
                    'message' => 'Atendimento concluído não pode ser alterado.'
                ]);
            }

            $atendimento->update([
                'status_atual' => $request->valor
            ]);

            AtendimentoStatusHistorico::create([
                'atendimento_id' => $atendimento->id,
                'status'         => $request->valor,
                'observacao'     => 'Alteração via fila',
                'user_id'        => Auth::id(),
            ]);

            // Notificar alteração de status
            if ($atendimento->funcionario_id) {
                $tecnico = Funcionario::with('user')->find($atendimento->funcionario_id);
                if ($tecnico && $tecnico->user) {
                    $this->notificacaoService->enviarParaUsuario(
                        $tecnico->user,
                        "Status Atualizado #{$atendimento->numero_atendimento}",
                        "O atendimento foi alterado para: " . strtoupper((string) $request->valor),
                        ['type' => 'status_atualizado', 'id' => $atendimento->id, 'status' => $request->valor]
                    );
                }
            }

            $this->registrarLog('chamado status alterado', $atendimento, [
                'numero'      => $atendimento->numero_atendimento,
                'status_novo' => $request->valor,
            ]);

            return response()->json(['success' => true]);
        }

        // PRIORIDADE, TÉCNICO OU EMPRESA
        $atendimento->update([
            $request->campo => $request->valor
        ]);

        // Notificar novo técnico se for o caso
        if ($request->campo === 'funcionario_id' && filled($request->valor)) {
            $tecnico = Funcionario::with('user')->find($request->valor);
            if ($tecnico && $tecnico->user) {
                $this->notificacaoService->enviarParaUsuario(
                    $tecnico->user,
                    "Chamado Atribuído #{$atendimento->numero_atendimento}",
                    "Você foi designado para o atendimento: {$atendimento->assunto->nome}",
                    ['type' => 'atribuicao_tecnico', 'id' => $atendimento->id]
                );
            }
        }

        $this->registrarLog('chamado campo atualizado', $atendimento, [
            'numero' => $atendimento->numero_atendimento,
            'campo'  => $request->campo,
            'valor'  => $request->valor,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Atendimento $atendimento)
    {
        $this->registrarLog('chamado excluído', null, [
            'id'     => $atendimento->id,
            'numero' => $atendimento->numero_atendimento,
        ]);

        $atendimento->delete();

        return redirect()
            ->route('atendimentos.index')
            ->with('success', 'Atendimento excluído com sucesso!');
    }

    /**
     * Reagendar agendamento de atendimento
     */
    public function reagendarAgendamento(
        Request $request,
        Atendimento $atendimento,
        AgendaTecnicaService $agendaService
    ) {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial' && $user->tipo !== 'tecnico',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'data_agendamento' => ['required', 'date_format:Y-m-d'],
            'periodo_agendamento' => ['required', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'duracao_horas' => ['required', 'integer', 'min:1', 'max:9'],
        ]);

        if (! $atendimento->funcionario_id) {
            throw ValidationException::withMessages([
                'atendimento' => 'Atendimento não possui técnico atribuído.',
            ]);
        }

        DB::transaction(function () use ($request, $atendimento, $agendaService) {
            $funcionarioAntigo = Funcionario::find($atendimento->funcionario_id);
            $funcionarioNovo = Funcionario::find($request->funcionario_id);

            $agendaService->reprogramarAtendimento(
                $atendimento,
                (int) $request->funcionario_id,
                $request->data_agendamento,
                $request->periodo_agendamento,
                $request->hora_inicio,
                (int) $request->duracao_horas
            );

            // Atualizar data_atendimento para sincronizar com agendamento
            $atendimento->update([
                'data_atendimento' => $request->data_agendamento,
            ]);

            $mensagem = "Agendamento reagendado com sucesso.";

            if ($funcionarioAntigo?->id !== $funcionarioNovo?->id) {
                $nomeAntigo = $funcionarioAntigo?->nome ?? 'Técnico removido';
                $nomeNovo   = $funcionarioNovo?->nome   ?? 'Técnico desconhecido';
                $mensagem  .= " Técnico alterado de {$nomeAntigo} para {$nomeNovo}.";
            }

            session()->flash('success', $mensagem);
        });

        return back();
    }
}
