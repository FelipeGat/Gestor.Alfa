<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AtendimentoAndamento;

class AtendimentoAndamentoFotoController extends Controller
{
    public function store(Request $request, AtendimentoAndamento $andamento)
    {
        $request->validate(
            ['fotos.*' => 'required|image|max:2048'],
            ['fotos.*.image' => 'O arquivo deve ser uma imagem válida.']
        );

        foreach ($request->file('fotos') as $foto) {
            $path = $foto->store(
            "andamentos/atendimento_{$andamento->atendimento_id}",
            'public'
            );

            $andamento->fotos()->create([
                'arquivo' => $path,
            ]);
        }

        return back()->with('success', 'Fotos anexadas com sucesso.');
    }
}