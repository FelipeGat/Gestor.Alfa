<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrcamentoController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $query = Orcamento::with(['empresa', 'cliente'])
            ->orderByDesc('created_at');

        // Comercial: apenas empresas vinculadas
        if ($user->tipo === 'comercial') {
            $empresaIds = $user->empresas->pluck('id');

            // segurança extra
            if ($empresaIds->isEmpty()) {
                $orcamentos = collect();
                return view('orcamentos.index', compact('orcamentos'));
            }

            $query->whereIn('empresa_id', $empresaIds);
        }

        // Admin vê tudo
        $orcamentos = $query->get();

        return view('orcamentos.index', compact('orcamentos'));
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

        $empresas = Empresa::orderBy('nome_fantasia')->get();
        $clientes = Cliente::orderBy('nome')->get();

        return view('orcamentos.create', compact('empresas', 'clientes'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'empresa_id'      => 'required|exists:empresas,id',
            'descricao'       => 'required|string|max:255',
            'validade'        => 'nullable|date|after_or_equal:' . now()->addDays(5)->format('Y-m-d'),
            'observacoes'     => 'nullable|string',
            'cliente_id'      => 'nullable|exists:clientes,id',
            'desconto'        => 'nullable|numeric|min:0',
            'taxas'           => 'nullable|numeric|min:0',
            'forma_pagamento' => 'nullable|string|max:50',
        ]);

        // Geração segura do número
        $numero = \App\Models\Orcamento::gerarNumero($request->empresa_id);

        // Cálculo simples do valor total (por enquanto)
        $valorTotal = ($request->valor_total ?? 0)
            - ($request->desconto ?? 0)
            + ($request->taxas ?? 0);

        $orcamento = \App\Models\Orcamento::create([
            'empresa_id'       => $request->empresa_id,
            'numero_orcamento' => $numero,
            'descricao'        => $request->descricao,
            'validade'         => $request->validade,
            'status'           => 'em_elaboracao',
            'cliente_id'       => $request->cliente_id,
            'valor_total'      => $valorTotal,
            'desconto'         => $request->desconto,
            'taxas'            => $request->taxas,
            'forma_pagamento'  => $request->forma_pagamento,
            'observacoes'      => $request->observacoes,
            'created_by'       => $user->id,
        ]);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento criado com sucesso!');
    }


    public function edit(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        return view('orcamentos.edit', compact('orcamento'));
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'status'      => 'required|string',
            'valor_total' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable|string',
        ]);

        $orcamento->update([
            'status'      => $request->status,
            'valor_total' => $request->valor_total,
            'observacoes' => $request->observacoes,
        ]);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento atualizado com sucesso!');
    }

    public function destroy(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $orcamento->delete();

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso!');
    }

    public function gerarNumero($empresaId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403
        );

        return response()->json([
            'numero' => Orcamento::gerarNumero((int) $empresaId)
        ]);
    }

}