<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('empresas', 'ler'),
            403
        );

        $query = Empresa::query();

        // Filtro por razão social ou nome fantasia
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%");
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('ativo', true);
            }

            if ($request->status === 'inativo') {
                $query->where('ativo', false);
            }
        }

        $empresas = $query
            ->orderBy('razao_social')
            ->paginate(15)
            ->appends($request->query());

        $totais = [
            'total' => Empresa::count(),
            'ativos' => Empresa::where('ativo', true)->count(),
            'inativos' => Empresa::where('ativo', false)->count(),
        ];

        return view('empresas.index', compact('empresas', 'totais'));
    }

    public function create()
    {

        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('empresas', 'incluir'),
            403
        );

        return view('empresas.create');
    }

    public function ajaxCreate()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('empresas', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        return view('empresas._form');
    }

    public function ajaxEdit(Empresa $empresa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('empresas', 'editar'),
            403,
            'Acesso não autorizado'
        );

        return view('empresas._form', compact('empresa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas',
        ]);

        Empresa::create([
            'razao_social' => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia,
            'cnpj' => $request->cnpj,
            'endereco' => $request->endereco,
            'email_comercial' => $request->email_comercial,
            'email_administrativo' => $request->email_administrativo,
            'telefone_comercial' => $request->telefone_comercial,
            'ativo' => $request->ativo ?? true,
        ]);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa cadastrada com sucesso!');
    }

    public function edit(Empresa $empresa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('empresas', 'editar'),
            403
        );

        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj,'.$empresa->id,
        ]);

        $empresa->update([
            'razao_social' => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia,
            'cnpj' => $request->cnpj,
            'endereco' => $request->endereco,
            'email_comercial' => $request->email_comercial,
            'email_administrativo' => $request->email_administrativo,
            'telefone_comercial' => $request->telefone_comercial,
            'ativo' => $request->boolean('ativo'),
        ]);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    public function destroy(Empresa $empresa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('empresas', 'excluir'),
            403
        );

        $empresa->delete();

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa excluída com sucesso!');
    }

    public function assuntos(Empresa $empresa)
    {
        return $empresa->assuntos()
            ->where('ativo', true)
            ->orderBy('categoria')
            ->orderBy('subcategoria')
            ->orderBy('nome')
            ->get([
                'id',
                'nome',
                'categoria',
                'subcategoria',
            ]);
    }

    /**
     * API - Retorna lista de empresas para select
     */
    public function apiList()
    {
        $empresas = Empresa::where('ativo', true)
            ->select('id', 'razao_social', 'nome_fantasia')
            ->orderBy('nome_fantasia')
            ->get();

        return response()->json($empresas);
    }
}
