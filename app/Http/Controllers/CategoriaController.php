<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Subcategoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    private function isAdmin(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->tipo === 'admin' || $user->isAdminPanel();
    }

    private function canEdit(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->isAdmin() || $user->canPermissao('categorias', 'editar');
    }

    private function canDelete(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->isAdmin() || $user->canPermissao('categorias', 'excluir');
    }

    public function index(Request $request)
    {
        abort_if(
            ! $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'ler'),
            403,
            'Acesso não autorizado'
        );

        $ativas = $request->get('ativas', 'todas');

        $categoriasQuery = Categoria::with('subcategorias.contas');

        if ($ativas === 'ativas') {
            $categoriasQuery->where('ativo', true);
        } elseif ($ativas === 'inativas') {
            $categoriasQuery->where('ativo', false);
        }

        $categorias = $categoriasQuery->orderBy('nome')->get();

        $subcategoriasQuery = Subcategoria::with('categoria', 'contas');

        if ($ativas === 'ativas') {
            $subcategoriasQuery->where('ativo', true);
        } elseif ($ativas === 'inativas') {
            $subcategoriasQuery->where('ativo', false);
        }

        $subcategorias = $subcategoriasQuery->orderBy('nome')->get();

        $contasQuery = Conta::with('subcategoria.categoria');

        if ($ativas === 'ativas') {
            $contasQuery->where('ativo', true);
        } elseif ($ativas === 'inativas') {
            $contasQuery->where('ativo', false);
        }

        $contas = $contasQuery->orderBy('nome')->get();

        $todasCategorias = Categoria::where('ativo', true)->orderBy('nome')->get();
        $todasSubcategorias = Subcategoria::where('ativo', true)->orderBy('nome')->get();

        return view('categorias.index', compact(
            'categorias',
            'subcategorias',
            'contas',
            'todasCategorias',
            'todasSubcategorias',
            'ativas'
        ));
    }

    public function storeCategoria(Request $request)
    {
        abort_if(
            ! $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome',
            'tipo' => 'required|string|in:FIXA,VARIAVEL,INVESTIMENTO',
            'ativo' => 'required|in:0,1',
        ]);

        Categoria::create([
            'nome' => $request->nome,
            'tipo' => $request->tipo,
            'ativo' => $request->ativo,
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function updateCategoria(Request $request, Categoria $categoria)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,'.$categoria->id,
            'tipo' => 'required|string|in:FIXA,VARIAVEL,INVESTIMENTO',
            'ativo' => 'required|in:0,1',
        ]);

        $categoria->update([
            'nome' => $request->nome,
            'tipo' => $request->tipo,
            'ativo' => $request->ativo,
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroyCategoria(Categoria $categoria)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso.');
    }

    public function storeSubcategoria(Request $request)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nome' => 'required|string|max:255',
            'ativo' => 'required|in:0,1',
        ]);

        Subcategoria::create([
            'categoria_id' => $request->categoria_id,
            'nome' => $request->nome,
            'ativo' => $request->ativo,
        ]);

        return redirect()->route('categorias.index')->with('success', 'Subcategoria criada com sucesso.');
    }

    public function updateSubcategoria(Request $request, Subcategoria $subcategoria)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nome' => 'required|string|max:255',
            'ativo' => 'required|in:0,1',
        ]);

        $subcategoria->update([
            'categoria_id' => $request->categoria_id,
            'nome' => $request->nome,
            'ativo' => $request->ativo,
        ]);

        return redirect()->route('categorias.index')->with('success', 'Subcategoria atualizada com sucesso.');
    }

    public function destroySubcategoria(Subcategoria $subcategoria)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        $subcategoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Subcategoria excluída com sucesso.');
    }

    public function storeConta(Request $request)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'subcategoria_id' => 'required|exists:subcategorias,id',
            'nome' => 'required|string|max:255',
            'ativo' => 'required|in:0,1',
        ]);

        Conta::create([
            'subcategoria_id' => $request->subcategoria_id,
            'nome' => $request->nome,
            'ativo' => $request->ativo,
        ]);

        return redirect()->route('categorias.index')->with('success', 'Conta criada com sucesso.');
    }

    public function updateConta(Request $request, Conta $conta)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'subcategoria_id' => 'required|exists:subcategorias,id',
            'nome' => 'required|string|max:255',
            'ativo' => 'required|in:0,1',
        ]);

        $conta->update([
            'subcategoria_id' => $request->subcategoria_id,
            'nome' => $request->nome,
            'ativo' => $request->ativo,
        ]);

        return redirect()->route('categorias.index')->with('success', 'Conta atualizada com sucesso.');
    }

    public function destroyConta(Conta $conta)
    {
        $user = Auth::user();

        abort_if(
            $this->isAdmin() && ! Auth::user()->canPermissao('categorias', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        $conta->delete();

        return redirect()->route('categorias.index')->with('success', 'Conta excluída com sucesso.');
    }
}
