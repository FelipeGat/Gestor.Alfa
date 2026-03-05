<?php

namespace App\Http\Controllers;

use App\Services\AgendaTecnicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AgendaTecnicaController extends Controller
{
    public function periodosDisponiveis(Request $request, AgendaTecnicaService $agendaService)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data' => 'required|date_format:Y-m-d',
        ]);

        $funcionarioId = (int) $request->funcionario_id;
        $data = $request->data;

        $periodos = $agendaService->periodosDisponiveis($funcionarioId, $data);

        return Response::json([
            'periodos_disponiveis' => $periodos
        ]);
    }
    // ...outros métodos existentes...

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

    /**
     * Reprogramar agendamento de atendimento vinculado a orçamento
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
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'data_agendamento' => ['required', 'date_format:Y-m-d'],
            'periodo_agendamento' => ['required', Rule::in(array_keys(AgendaTecnicaService::PERIODOS))],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'duracao_horas' => ['required', 'integer', 'min:1', 'max:9'],
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
            $funcionarioNovo = Funcionario::find($request->funcionario_id);

            $agendaService->reprogramarAtendimento(
                $atendimento,
                (int) $request->funcionario_id,
                $request->data_agendamento,
                $request->periodo_agendamento,
                $request->hora_inicio,
                (int) $request->duracao_horas
            );

            $orcamento->update([
                'data_agendamento' => $request->data_agendamento,
            ]);

            $mensagem = "Agendamento reprogramado com sucesso.";
            
            if ($funcionarioAntigo?->id !== $funcionarioNovo?->id) {
                $mensagem .= " Técnico alterado de {$funcionarioAntigo->nome} para {$funcionarioNovo->nome}.";
            }

            session()->flash('success', $mensagem);
        });

        return back();
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
