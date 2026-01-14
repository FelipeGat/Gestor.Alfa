<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PortalFuncionarioController extends Controller
{

    public function dashboard()
    {
        $funcionarioId = Auth::user()->funcionario_id;

        $baseQuery = Atendimento::where('funcionario_id', $funcionarioId);

        // Em aberto (tudo menos concluído)
        $totalEmAberto = (clone $baseQuery)
            ->whereNotIn('status_atual', ['concluido'])
            ->count();

        // Finalizados
        $totalFinalizados = (clone $baseQuery)
            ->where('status_atual', 'concluido')
            ->count();

        // Por status
        $porStatus = (clone $baseQuery)
            ->select('status_atual', DB::raw('count(*) as total'))
            ->groupBy('status_atual')
            ->pluck('total', 'status_atual');

        // Por assunto
        $porAssunto = (clone $baseQuery)
            ->join('assuntos', 'atendimentos.assunto_id', '=', 'assuntos.id')
            ->select('assuntos.nome', DB::raw('count(*) as total'))
            ->groupBy('assuntos.nome')
            ->pluck('total', 'assuntos.nome');

        // Por prioridade
        $porPrioridade = (clone $baseQuery)
            ->select('prioridade', DB::raw('count(*) as total'))
            ->groupBy('prioridade')
            ->pluck('total', 'prioridade');

        return view('portal-funcionario.dashboard', compact(
            'totalEmAberto',
            'totalFinalizados',
            'porStatus',
            'porAssunto',
            'porPrioridade'
        ));
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

    public function atendimentos()
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

        return view('portal-funcionario.atendimentos.index', compact('atendimentos'));
    }

}