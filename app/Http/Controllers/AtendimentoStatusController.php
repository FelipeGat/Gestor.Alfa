<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AtendimentoStatusController extends Controller
{
    public function update(Request $request, Atendimento $atendimento)
    {
        if ($atendimento->status_atual === 'concluido') {
            return back()->withErrors('Atendimento concluído não pode ser alterado.');
        }

       $request->validate(
            [
                'status'     => ['required', Rule::in([
                    'orcamento',
                    'aberto',
                    'em_atendimento',
                    'pendente_cliente',
                    'pendente_fornecedor',
                    'garantia',
                    'concluido'
                ])],
                'prioridade' => ['required', Rule::in(['baixa', 'media', 'alta'])],
                'descricao'  => ['required', 'string', 'min:5'],
            ],
            [
                'status.required'     => 'O status do atendimento é obrigatório.',
                'status.in'           => 'O status selecionado é inválido.',
                'prioridade.required' => 'A prioridade é obrigatória.',
                'prioridade.in'       => 'A prioridade selecionada é inválida.',
                'descricao.required'  => 'A justificativa é obrigatória.',
                'descricao.min'       => 'A justificativa deve ter no mínimo :min caracteres.',
            ]
        );


        // Atualiza atendimento
        $statusAnterior = $atendimento->status_atual;

        $atendimento->update([
            'status_atual' => $request->status,
            'prioridade'   => $request->prioridade,
        ]);

        // Registra andamento (obrigatório)
        $atendimento->andamentos()->create([
            'user_id'   => Auth::id(),
            'descricao' => "Alteração de status/prioridade:\n" . $request->descricao,
        ]);

        // Registra histórico de status (regra de negócio)
        $atendimento->statusHistoricos()->create([
            'status_anterior' => $statusAnterior,
            'status_novo'     => $request->status,
            'user_id'         => Auth::id(),
        ]);

        return redirect()
            ->route('atendimentos.edit', $atendimento)
            ->with('success', 'Status e prioridade atualizados com sucesso.');
    }
}