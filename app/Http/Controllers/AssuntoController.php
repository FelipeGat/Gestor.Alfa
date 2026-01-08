<?php

namespace App\Http\Controllers;

use App\Models\Assunto;
use Illuminate\Http\Request;

class AssuntoController extends Controller
{
    public function index(Request $request)
    {
        $query = Assunto::query();

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $assuntos = $query->orderBy('nome')->get();

        return view('assuntos.index', compact('assuntos'));
    }

    public function create()
    {
        return view('assuntos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Assunto::create([
            'nome' => $request->nome,
            'ativo' => $request->ativo ?? true,
        ]);

        return redirect()->route('assuntos.index')
            ->with('success', 'Assunto cadastrado com sucesso.');
    }

    public function edit(Assunto $assunto)
    {
        return view('assuntos.edit', compact('assunto'));
    }

    public function update(Request $request, Assunto $assunto)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $assunto->update([
            'nome' => $request->nome,
            'ativo' => $request->ativo ?? false,
        ]);

        return redirect()->route('assuntos.index')
            ->with('success', 'Assunto atualizado com sucesso.');
    }

    public function destroy(Assunto $assunto)
    {
        $assunto->delete();

        return redirect()->route('assuntos.index')
            ->with('success', 'Assunto removido com sucesso.');
    }
}