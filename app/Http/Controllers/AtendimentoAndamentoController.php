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
            'fotos.*' => 'nullable|image|max:5120', // até 5MB por foto
        ]);

        $andamento = $atendimento->andamentos()->create([
            'user_id'   => Auth::id(),
            'descricao' => $request->descricao,
        ]);

        // Salvar fotos, se houver
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('atendimentos/fotos', 'public');
                $publicPath = 'storage/' . ltrim(str_replace('public/', '', $path), '/');
                $andamento->fotos()->create([
                    'arquivo' => $publicPath,
                ]);
            }
        }

        return redirect()
            ->route('atendimentos.edit', $atendimento)
            ->with('success', 'Andamento registrado com sucesso.');
    }
}
