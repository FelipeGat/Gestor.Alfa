<?php

namespace App\Http\Controllers;

use App\Models\ItemComercial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemComercialController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $query = ItemComercial::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $totalItemComercial    = ItemComercial::count();
        $itemAtivos            = ItemComercial::where('ativo', true)->count();
        $itemInativos          = ItemComercial::where('ativo', false)->count();

        $itemcomercial = $query
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('itemcomercial.index', compact(
            'itemcomercial',
            'totalItemComercial',
            'itemAtivos',
            'itemInativos'
        ));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        return view('itemcomercial.create');
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'tipo'           => 'required|in:produto,servico',
            'nome'           => 'required|string|max:255',
            'preco_venda'    => 'required|numeric|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'unidade_medida' => 'required|string',
            'estoque_atual'  => 'nullable|integer|min:0',
            'gerencia_estoque' => 'nullable|boolean',
        ]);

        ItemComercial::create([
            'tipo'            => $request->tipo,
            'nome'            => $request->nome,
            'sku_ou_referencia'=> $request->sku_ou_referencia,
            'codigo_barras_ean'=> $request->codigo_barras_ean,
            'categoria_id'    => $request->categoria_id,
            'preco_venda'     => $request->preco_venda,
            'preco_custo'     => $request->preco_custo,
            'unidade_medida'  => $request->unidade_medida,
            'estoque_atual'   => $request->estoque_atual,
            'estoque_minimo'  => $request->estoque_minimo,
            'gerencia_estoque'=> $request->boolean('gerencia_estoque'),
            'ativo'           => true,
        ]);

        return redirect()
            ->route('itemcomercial.index')
            ->with('success', 'Item cadastrado com sucesso!');
    }

    public function edit(ItemComercial $item_comercial)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        return view('itemcomercial.edit', [
        'itemComercial' => $item_comercial
    ]);
    }

    public function update(Request $request, ItemComercial $itemComercial)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'tipo'           => 'required|in:produto,servico',
            'nome'           => 'required|string|max:255',
            'preco_venda'    => 'required|numeric|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'unidade_medida' => 'required|string',
        ]);

        $itemComercial->update([
            'tipo'            => $request->tipo,
            'nome'            => $request->nome,
            'sku_ou_referencia'=> $request->sku_ou_referencia,
            'codigo_barras_ean'=> $request->codigo_barras_ean,
            'categoria_id'    => $request->categoria_id,
            'preco_venda'     => $request->preco_venda,
            'preco_custo'     => $request->preco_custo,
            'unidade_medida'  => $request->unidade_medida,
            'estoque_atual'   => $request->estoque_atual,
            'estoque_minimo'  => $request->estoque_minimo,
            'gerencia_estoque'=> $request->boolean('gerencia_estoque'),
            'ativo'           => $request->boolean('ativo'),
        ]);

        return redirect()
            ->route('itemcomercial.index')
            ->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(ItemComercial $itemComercial)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $itemComercial->delete();

        return redirect()
            ->route('itemcomercial.index')
            ->with('success', 'Item excluído com sucesso!');
    }

    public function buscar(Request $request)
{
    /** @var User $user */
    $user = Auth::user();

    abort_if(
        !$user->isAdminPanel() && $user->tipo !== 'comercial',
        403,
        'Acesso não autorizado'
    );

    $q = trim((string) $request->query('q'));

    if ($q === '') {
        return response()->json([]);
    }

    $itens = ItemComercial::query()
        ->where('ativo', true)
        ->where(function ($query) use ($q) {
            $query->where('nome', 'like', "%{$q}%")
                  ->orWhere('sku_ou_referencia', 'like', "%{$q}%")
                  ->orWhere('codigo_barras_ean', 'like', "%{$q}%");
        })
        ->orderBy('tipo')
        ->orderBy('nome')
        ->limit(15)
        ->get([
            'id',
            'nome',
            'tipo',
            'preco_venda',
            'unidade_medida',
        ]);

    return response()->json(
        $itens->map(fn ($item) => [
            'id'            => $item->id,
            'nome'          => $item->nome,
            'tipo'          => $item->tipo,
            'preco_venda'   => (float) $item->preco_venda,
            'unidade_medida'=> $item->unidade_medida,
        ])
    );
}

}