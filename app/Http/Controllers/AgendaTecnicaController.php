<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\AtendimentoStatusHistorico;
use App\Models\Assunto;
use App\Models\Funcionario;
use App\Models\Orcamento;
use App\Models\User;
use App\Services\AgendaTecnicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AgendaTecnicaController extends Controller
{
    public function disponibilidade(Request $request): JsonResponse
    {
        $request->validate([
            'data' => ['required', 'date_format:Y-m-d'],
            'periodo' => ['nullable', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
        ]);

        $data = $request->string('data')->toString();
        $periodo = $request->string('periodo')->toString();

        $tecnicos = Funcionario::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $agendamentos = Atendimento::query()
            ->with(['cliente:id,nome,nome_fantasia,razao_social'])
            ->whereDate('data_inicio_agendamento', $data)
            ->when($periodo, fn ($q) => $q->where('periodo_agendamento', $periodo))
            ->whereNotNull('funcionario_id')
            ->orderBy('data_inicio_agendamento')
            ->get([
                'id',
                'funcionario_id',
                'numero_atendimento',
                'status_atual',
                'cliente_id',
                'nome_solicitante',
                'periodo_agendamento',
                'data_inicio_agendamento',
                'data_fim_agendamento',
            ])
            ->map(function (Atendimento $atendimento) {
                return [
                    'id' => $atendimento->id,
                    'funcionario_id' => $atendimento->funcionario_id,
                    'numero_atendimento' => $atendimento->numero_atendimento,
                    'cliente' => $atendimento->cliente?->nome_fantasia
                        ?? $atendimento->cliente?->nome
                        ?? $atendimento->nome_solicitante
                        ?? 'Sem cliente',
                    'status' => $atendimento->status_atual,
                    'periodo' => $atendimento->periodo_agendamento,
                    'inicio' => optional($atendimento->data_inicio_agendamento)->format('H:i'),
                    'fim' => optional($atendimento->data_fim_agendamento)->format('H:i'),
                ];
            })
            ->values();

        return response()->json([
            'tecnicos' => $tecnicos,
            'agendamentos' => $agendamentos,
        ]);
    }

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
            'orcamento_status' => ['required', Rule::in(['aprovado', 'agendado'])],
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'data_agendamento' => ['required', 'date_format:Y-m-d'],
            'periodo_agendamento' => ['required', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'duracao_horas' => ['required', 'integer', 'min:1', 'max:4'],
        ]);

        DB::transaction(function () use ($request, $orcamento, $agendaService) {
            $atendimento = $orcamento->atendimento;

            if (! $atendimento) {
                $assuntoId = $this->resolverAssuntoIdParaOrcamento($orcamento);

                if (! $assuntoId) {
                    throw ValidationException::withMessages([
                        'orcamento' => 'Não foi possível agendar o orçamento: cadastre ao menos um assunto para a empresa selecionada.',
                    ]);
                }

                $atendimento = Atendimento::create([
                    'numero_atendimento' => $this->proximoNumeroAtendimento(),
                    'cliente_id' => $orcamento->cliente_id,
                    'nome_solicitante' => $orcamento->nome_cliente,
                    'assunto_id' => $assuntoId,
                    'descricao' => 'Atendimento gerado automaticamente a partir do orçamento ' . $orcamento->numero_orcamento,
                    'prioridade' => 'media',
                    'empresa_id' => $orcamento->empresa_id,
                    'status_atual' => 'aberto',
                    'is_orcamento' => true,
                    'data_atendimento' => now(),
                ]);

                AtendimentoStatusHistorico::create([
                    'atendimento_id' => $atendimento->id,
                    'status' => 'aberto',
                    'observacao' => 'Atendimento criado automaticamente a partir do orçamento.',
                    'user_id' => Auth::id(),
                ]);
            }

            $agendaService->agendarAtendimento(
                $atendimento,
                (int) $request->funcionario_id,
                $request->data_agendamento,
                $request->periodo_agendamento,
                $request->hora_inicio,
                (int) $request->duracao_horas
            );

            $orcamento->update([
                'status' => $request->orcamento_status,
                'atendimento_id' => $atendimento->id,
                'data_agendamento' => $request->data_agendamento,
                'data_aprovacao' => $orcamento->data_aprovacao ?? now(),
            ]);
        });

        return back()->with('success', 'Orçamento atualizado e atendimento agendado com sucesso.');
    }

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
