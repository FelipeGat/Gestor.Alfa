<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtendimentoAndamentoController extends Controller
{
    public function store(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'descricao' => 'required|string|min:5',
        ]);

        $atendimento->andamentos()->create([
            'user_id'   => Auth::id(),
            'descricao' => $request->descricao,
        ]);

        return redirect()
            ->route('atendimentos.edit', $atendimento)
            ->with('success', 'Andamento registrado com sucesso.');
    }
}