<?php

namespace App\Http\Controllers;

use App\Models\Assunto;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssuntoController extends Controller
{
    public function index(Request $request)
{
    /** @var User $user */
    $user = Auth::user();

    abort_if(
        !$user->canPermissao('assuntos', 'ler'),
        403
    );

    $query = Assunto::with('empresa');

    if ($request->filled('search')) {
        $query->where('nome', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('status')) {
        $query->where('ativo', $request->status === 'ativo');
    }

    if ($request->filled('empresa_id')) {
        $query->where('empresa_id', $request->empresa_id);
    }

    $assuntos = $query->orderBy('nome')->get();

    $empresas = Empresa::where('ativo', true)
        ->orderBy('nome_fantasia')
        ->get();

    return view('assuntos.index', compact('assuntos', 'empresas'));
}

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->canPermissao('assuntos', 'incluir'),
            403
        );

        $empresas = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        return view('assuntos.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id'   => 'required|exists:empresas,id',
            'nome'         => 'required|string|max:255',
            'tipo'         => 'required|in:SERVICO,VENDA',
            'categoria'    => 'required|string|max:255',
            'subcategoria' => 'required|string|max:255',
            'ativo'        => 'required|boolean',
        ]);

        Assunto::create([
            'empresa_id'   => $request->empresa_id,
            'nome'         => $request->nome,
            'tipo'         => $request->tipo,
            'categoria'    => $request->categoria,
            'subcategoria' => $request->subcategoria,
            'ativo'        => $request->ativo,
        ]);

        return redirect()
            ->route('assuntos.index')
            ->with('success', 'Assunto cadastrado com sucesso.');
    }

    public function edit(Assunto $assunto)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->canPermissao('assuntos', 'editar'),
            403
        );

        $empresas = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        return view('assuntos.edit', compact('assunto', 'empresas'));
    }

    public function update(Request $request, Assunto $assunto)
    {
        $request->validate([
            'empresa_id'   => 'required|exists:empresas,id',
            'nome'         => 'required|string|max:255',
            'tipo'         => 'required|in:SERVICO,VENDA',
            'categoria'    => 'required|string|max:255',
            'subcategoria' => 'required|string|max:255',
            'ativo'        => 'required|boolean',
        ]);

        $assunto->update([
            'empresa_id'   => $request->empresa_id,
            'nome'         => $request->nome,
            'tipo'         => $request->tipo,
            'categoria'    => $request->categoria,
            'subcategoria' => $request->subcategoria,
            'ativo'        => $request->ativo,
        ]);

        return redirect()
            ->route('assuntos.index')
            ->with('success', 'Assunto atualizado com sucesso.');
    }

    public function destroy(Assunto $assunto)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->canPermissao('assuntos', 'editar'),
            403
        );

        $assunto->delete();

        return redirect()
            ->route('assuntos.index')
            ->with('success', 'Assunto removido com sucesso.');
    }
}