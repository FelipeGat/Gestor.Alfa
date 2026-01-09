<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Support\Facades\Auth;

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
}