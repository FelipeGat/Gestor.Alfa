<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PortalFuncionarioController extends Controller
{
    public function dashboard()
    {
        $funcionarioId = Auth::user()->funcionario_id;

        $atendimentos = Atendimento::with([
                'cliente',
                'assunto',
                'empresa'
            ])
            ->where('funcionario_id', $funcionarioId)
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderByDesc('data_atendimento')
            ->get();

        return view('portal-funcionario.dashboard', compact('atendimentos'));
    }

    public function agenda()
    {
        return view('portal-funcionario.agenda');
    }

    public function show(Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        // Técnico só vê atendimento dele
        abort_if(
            $atendimento->funcionario_id !== $funcionarioId,
            403
        );

        return view('portal-funcionario.atendimentos.show', compact('atendimento'));
    }

    /**
     * 🔧 Técnico pode APENAS enviar atendimento para FINALIZAÇÃO
     */
    public function enviarParaFinalizacao(Request $request, Atendimento $atendimento)
    {
        $funcionarioId = Auth::user()->funcionario_id;

        // Técnico só atua no atendimento dele
        abort_if(
            $atendimento->funcionario_id !== $funcionarioId,
            403
        );

        // Técnico só pode enviar para FINALIZAÇÃO
        $request->validate([
            'status' => ['required', Rule::in(['finalizacao'])],
        ]);

        // caso forcem payload)
        abort_if(
            $request->status !== 'finalizacao',
            403
        );

        // Atualiza status
        $atendimento->update([
            'status_atual' => 'finalizacao',
        ]);

        // Registra histórico automático
        $atendimento->andamentos()->create([
            'user_id'   => Auth::id(),
            'descricao' => 'Atendimento enviado para finalização pelo técnico.',
        ]);

        return back()->with('success', 'Atendimento enviado para avaliação do administrador.');
    }
}