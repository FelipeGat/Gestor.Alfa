<?php

namespace App\Http\Controllers;

use App\Models\Assunto;
use App\Models\Atendimento;
use App\Models\Funcionario;
use App\Models\Orcamento;
use App\Models\User;
use App\Services\AgendaTecnicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AgendaTecnicaController extends Controller
{
    /**
     * Retorna períodos disponíveis para um técnico em uma data (legado / específico por técnico).
     */
    public function periodosDisponiveis(Request $request, AgendaTecnicaService $agendaService): JsonResponse
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data'           => 'required|date_format:Y-m-d',
        ]);

        $periodos = $agendaService->periodosDisponiveis(
            (int) $request->funcionario_id,
            $request->data
        );

        return Response::json(['periodos_disponiveis' => $periodos]);
    }

    /**
     * Disponibilidade geral: retorna todos os técnicos ativos + agendamentos do dia.
     * Chamado via AJAX nos modais de agendamento (orçamentos, atendimentos).
     */
    public function disponibilidade(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|date_format:Y-m-d',
        ]);

        $data    = $request->data;
        $periodo = $request->get('periodo');

        // Todos os técnicos ativos
        $tecnicos = Funcionario::where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->toArray();

        // Atendimentos agendados para a data
        $query = Atendimento::with(['cliente'])
            ->whereNotNull('data_inicio_agendamento')
            ->whereDate('data_inicio_agendamento', $data)
            ->whereNotIn('status_atual', ['concluido', 'cancelado']);

        if ($periodo && isset(AgendaTecnicaService::PERIODOS[$periodo])) {
            $limites = AgendaTecnicaService::PERIODOS[$periodo];
            $query->whereTime('data_inicio_agendamento', '>=', $limites['inicio'])
                  ->whereTime('data_inicio_agendamento', '<',  $limites['fim']);
        }

        $agendamentos = $query->get()->flatMap(fn (Atendimento $at) => collect(
            // Técnico principal
            [['funcionario_id' => $at->funcionario_id]]
        )->merge(
            // Técnicos adicionais via pivot
            \DB::table('atendimento_tecnicos')
                ->where('atendimento_id', $at->id)
                ->pluck('funcionario_id')
                ->map(fn ($fid) => ['funcionario_id' => $fid])
        )->map(fn ($row) => [
            'funcionario_id'     => $row['funcionario_id'],
            'inicio'             => optional($at->data_inicio_agendamento)->format('H:i'),
            'fim'                => optional($at->data_fim_agendamento)->format('H:i'),
            'numero_atendimento' => $at->numero_atendimento,
            'cliente'            => $at->cliente?->nome ?? '—',
        ]))->values()->toArray();

        return Response::json([
            'tecnicos'     => $tecnicos,
            'agendamentos' => $agendamentos,
        ]);
    }

    /**
     * Agendar técnico para um orçamento: cria atendimento (se necessário) e agenda.
     */
    public function agendarOrcamento(
        Request $request,
        Orcamento $orcamento,
        AgendaTecnicaService $agendaService
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'funcionario_id'      => ['required', 'exists:funcionarios,id'],
            'data_agendamento'    => ['required', 'date_format:Y-m-d'],
            'periodo_agendamento' => ['required', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
            'hora_inicio'         => ['required', 'date_format:H:i'],
            'duracao_horas'       => ['required', 'integer', 'min:1', 'max:9'],
        ]);

        DB::transaction(function () use ($request, $orcamento, $agendaService) {
            // Busca ou cria atendimento vinculado
            $atendimento = $orcamento->atendimento;

            if (! $atendimento) {
                $atendimento = Atendimento::create([
                    'numero_atendimento' => $this->proximoNumeroAtendimento(),
                    'cliente_id'         => $orcamento->cliente_id,
                    'empresa_id'         => $orcamento->empresa_id,
                    'assunto_id'         => $this->resolverAssuntoIdParaOrcamento($orcamento),
                    'nome_solicitante'   => $orcamento->nome_cliente,
                    'data_atendimento'   => $request->data_agendamento,
                    'descricao'          => $orcamento->descricao
                        ?? 'Atendimento vinculado ao Orçamento #' . $orcamento->numero_orcamento,
                    'status_atual'       => 'aberto',
                    'prioridade'         => 'media',
                    'funcionario_id'     => (int) $request->funcionario_id,
                ]);

                $orcamento->update(['atendimento_id' => $atendimento->id]);
            }

            // Agenda o atendimento com técnico, data, período e horário
            $agendaService->agendarAtendimento(
                $atendimento,
                (int) $request->funcionario_id,
                $request->data_agendamento,
                $request->periodo_agendamento,
                $request->hora_inicio,
                (int) $request->duracao_horas
            );

            // Salvar técnicos adicionais (excluindo o técnico principal)
            $tecnicosAdicionais = array_filter(
                (array) $request->input('tecnicos_adicionais', []),
                fn ($id) => is_numeric($id) && (int) $id !== (int) $request->funcionario_id
            );
            $atendimento->tecnicosAdicionais()->sync(array_values($tecnicosAdicionais));

            // Atualiza status do orçamento
            $novoStatus  = $request->input('orcamento_status', 'agendado');
            $dadosUpdate = [
                'status'          => $novoStatus,
                'data_agendamento' => $request->data_agendamento,
            ];
            if (! $orcamento->data_aprovacao) {
                $dadosUpdate['data_aprovacao'] = now();
            }
            $orcamento->update($dadosUpdate);

            $funcionario = Funcionario::find((int) $request->funcionario_id);
            session()->flash('success', "Técnico {$funcionario?->nome} agendado para {$request->data_agendamento}.");
        });

        return back();
    }

    /**
     * Reprogramar agendamento de atendimento vinculado a orçamento.
     */
    public function reprogramarAgendamento(
        Request $request,
        Orcamento $orcamento,
        AgendaTecnicaService $agendaService
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'funcionario_id'      => ['required', 'exists:funcionarios,id'],
            'data_agendamento'    => ['required', 'date_format:Y-m-d'],
            'periodo_agendamento' => ['required', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
            'hora_inicio'         => ['required', 'date_format:H:i'],
            'duracao_horas'       => ['required', 'integer', 'min:1', 'max:9'],
        ]);

        $atendimento = $orcamento->atendimento;

        if (! $atendimento) {
            throw ValidationException::withMessages([
                'orcamento' => 'Não foi possível reprogramar: orçamento não possui atendimento vinculado.',
            ]);
        }

        if (! $atendimento->funcionario_id) {
            throw ValidationException::withMessages([
                'atendimento' => 'Atendimento não possui técnico atribuído.',
            ]);
        }

        DB::transaction(function () use ($request, $orcamento, $atendimento, $agendaService) {
            $funcionarioAntigo = Funcionario::find($atendimento->funcionario_id);
            $funcionarioNovo   = Funcionario::find($request->funcionario_id);

            $agendaService->reprogramarAtendimento(
                $atendimento,
                (int) $request->funcionario_id,
                $request->data_agendamento,
                $request->periodo_agendamento,
                $request->hora_inicio,
                (int) $request->duracao_horas
            );

            // Salvar técnicos adicionais (excluindo o técnico principal)
            $tecnicosAdicionais = array_filter(
                (array) $request->input('tecnicos_adicionais', []),
                fn ($id) => is_numeric($id) && (int) $id !== (int) $request->funcionario_id
            );
            $atendimento->tecnicosAdicionais()->sync(array_values($tecnicosAdicionais));

            $orcamento->update(['data_agendamento' => $request->data_agendamento]);

            $mensagem = 'Agendamento reprogramado com sucesso.';
            if ($funcionarioAntigo?->id !== $funcionarioNovo?->id) {
                $nomeAntigo = $funcionarioAntigo?->nome ?? 'desconhecido';
                $nomeNovo   = $funcionarioNovo?->nome ?? 'desconhecido';
                $mensagem  .= " Técnico alterado de {$nomeAntigo} para {$nomeNovo}.";
            }

            session()->flash('success', $mensagem);
        });

        return back();
    }

    // ─── Helpers privados ───────────────────────────────────────────────────

    private function proximoNumeroAtendimento(): string
    {
        return DB::transaction(function () {
            $ultimo = DB::table('atendimentos')
                ->lockForUpdate()
                ->orderByDesc('numero_atendimento')
                ->value('numero_atendimento');

            return (string) ((int) $ultimo + 1);
        });
    }

    private function resolverAssuntoIdParaOrcamento(Orcamento $orcamento): ?int
    {
        $assuntoAtivo = Assunto::query()
            ->where('empresa_id', $orcamento->empresa_id)
            ->where('ativo', true)
            ->orderBy('id')
            ->value('id');

        if ($assuntoAtivo) {
            return (int) $assuntoAtivo;
        }

        $assuntoQualquer = Assunto::query()
            ->where('empresa_id', $orcamento->empresa_id)
            ->orderBy('id')
            ->value('id');

        return $assuntoQualquer ? (int) $assuntoQualquer : null;
    }
}
