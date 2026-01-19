<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Atendimento;
use Illuminate\Support\Facades\DB;
use App\Models\ItemComercial;
use App\Models\OrcamentoItem;



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
        
        // ================= ORÇAMENTOS =================
        $query = Orcamento::with(['empresa', 'cliente'])
            ->orderByDesc('created_at');

        // Comercial: apenas empresas vinculadas
        if ($user->tipo === 'comercial') {
            $empresaIds = $user->empresas->pluck('id');

            // segurança extra
            if ($empresaIds->isEmpty()) {
                $orcamentos = collect();
                $atendimentosParaOrcamento = collect();
                return view('orcamentos.index', compact('orcamentos', 'atendimentosParaOrcamento'));
            }

            $query->whereIn('empresa_id', $empresaIds);
        }

        // Admin vê tudo
        $orcamentos = $query->get();
        
        // ================= ATENDIMENTOS AGUARDANDO ORÇAMENTO =================
            $atendimentosQuery = Atendimento::with(['cliente', 'empresa'])
                ->where('status_atual', 'orcamento')
                ->whereDoesntHave('orcamento');

            if ($user->tipo === 'comercial') {
                $atendimentosQuery->whereIn('empresa_id', $empresaIds);
            }

            $atendimentosParaOrcamento = $atendimentosQuery
                ->orderByDesc('created_at')
                ->get();

            return view(
                'orcamentos.index',
                compact('orcamentos', 'atendimentosParaOrcamento')
        );
    }



    public function create(Request $request)
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

        $atendimento = null;

            if ($request->filled('atendimento')) {
            $atendimento = Atendimento::with(['empresa', 'cliente'])
                ->where('id', $request->atendimento)
                ->where('status_atual', 'orcamento')
                ->firstOrFail();
        }

        return view(
            'orcamentos.create',
            compact('empresas', 'clientes', 'atendimento')
        );
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
            'empresa_id'      => 'required|exists:empresas,id',
            'descricao'       => 'required|string|max:255',
            'validade'        => 'nullable|date|after_or_equal:' . now()->addDays(5)->format('Y-m-d'),
            'cliente_tipo'    => 'required|in:cliente,pre_cliente',

            'desconto'        => 'nullable|numeric|min:0',
            'forma_pagamento' => 'nullable|string',

            'itens' => 'required|array|min:1',
            'itens.*.item_comercial_id' => 'required|exists:itens_comerciais,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $user) {

            // ---------- CLIENTE / PRÉ ----------
            $clienteId = null;
            $preClienteId = null;

            if ($request->cliente_tipo === 'cliente') {
                $clienteId = $request->cliente_id;
            } else {
                $preClienteId = $request->pre_cliente_id;
            }

            // ---------- ORÇAMENTO ----------
            $orcamento = Orcamento::create([
                'empresa_id'       => $request->empresa_id,
                'atendimento_id'   => $request->atendimento_id,
                'numero_orcamento' => Orcamento::gerarNumero($request->empresa_id),
                'descricao'        => $request->descricao,
                'validade'         => $request->validade,
                'status'           => 'em_elaboracao',
                'cliente_id'       => $clienteId,
                'pre_cliente_id'   => $preClienteId,
                'desconto'         => $request->desconto ?? 0,
                'taxas'            => $request->taxas ?? 0,
                'forma_pagamento'  => $request->forma_pagamento,
                'observacoes'      => $request->observacoes,
                'created_by'       => $user->id,
            
            ]);

            // ---------- ITENS ----------
            $totalServicos = 0;
            $totalProdutos = 0;

            foreach ($request->itens as $itemData) {

            $item = ItemComercial::findOrFail($itemData['item_comercial_id']);

            $valorUnitario = (float) $itemData['valor_unitario'];
            $subtotal = $itemData['quantidade'] * $valorUnitario;


                OrcamentoItem::create([
                    'orcamento_id'       => $orcamento->id,
                    'item_comercial_id'  => $item->id,
                    'tipo'               => $item->tipo,
                    'nome'               => $item->nome,
                    'quantidade'         => $itemData['quantidade'],
                    'valor_unitario'     => $valorUnitario,
                    'subtotal'           => $subtotal,
                ]);

                if ($item->tipo === 'servico') {
                    $totalServicos += $subtotal;
                } else {
                    $totalProdutos += $subtotal;
                }
            }

            // ---------- TOTAL FINAL ----------
            $valorTotal = $totalServicos
                + $totalProdutos
                - ($request->desconto ?? 0)
                + ($request->taxas ?? 0);

            $orcamento->update([
                'valor_total' => $valorTotal,
            ]);
        });

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
            403
        );

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        $orcamento->load([
            'itens' => function ($q) {
                $q->orderBy('id');
            }
        ]);

        // ================= ITENS PARA O JS =================
        $itensArray = $orcamento->itens->map(function ($item) {
            return [
                'id'           => $item->item_comercial_id,
                'nome'         => $item->nome,
                'tipo'         => $item->tipo,
                'preco_venda'  => (float) $item->valor_unitario,
                'quantidade'   => (int) $item->quantidade,
            ];
        })->values();

        return view(
            'orcamentos.edit',
            compact('orcamento', 'empresas', 'itensArray')
        );
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

        // ================= VALIDAÇÃO =================
        $request->validate([
            'empresa_id'      => 'required|exists:empresas,id',
            'descricao'       => 'required|string|max:255',
            'validade'        => 'nullable|date',
            'cliente_tipo'    => 'required|in:cliente,pre_cliente',

            'desconto'        => 'nullable|numeric|min:0',
            'taxas'           => 'nullable|numeric|min:0',
            'forma_pagamento' => 'nullable|string|max:50',
            'observacoes'     => 'nullable|string',

            'itens' => 'required|array|min:1',
            'itens.*.item_comercial_id' => 'required|exists:itens_comerciais,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $user, $orcamento) {

            // ---------- CLIENTE / PRÉ ----------
            $clienteId = null;
            $preClienteId = null;

            if ($request->cliente_tipo === 'cliente') {
                $clienteId = $request->cliente_id;
            } else {
                $preClienteId = $request->pre_cliente_id;
            }

            // ---------- ATUALIZAR ORÇAMENTO ----------
            $orcamento->update([
                'empresa_id'       => $request->empresa_id,
                'descricao'        => $request->descricao,
                'validade'         => $request->validade,
                'cliente_id'       => $clienteId,
                'pre_cliente_id'   => $preClienteId,
                'desconto'         => $request->desconto ?? 0,
                'taxas'            => $request->taxas ?? 0,
                'forma_pagamento'  => $request->forma_pagamento,
                'observacoes'      => $request->observacoes,
            ]);

            // ---------- REMOVER ITENS ANTIGOS ----------
            $orcamento->itens()->delete();

            // ---------- ADICIONAR NOVOS ITENS ----------
            $totalServicos = 0;
            $totalProdutos = 0;

            foreach ($request->itens as $itemData) {

                $item = ItemComercial::findOrFail($itemData['item_comercial_id']);

                $valorUnitario = (float) $itemData['valor_unitario'];
                $subtotal = $itemData['quantidade'] * $valorUnitario;


                OrcamentoItem::create([
                    'orcamento_id'       => $orcamento->id,
                    'item_comercial_id'  => $item->id,
                    'tipo'               => $item->tipo,
                    'nome'               => $item->nome,
                    'quantidade'         => $itemData['quantidade'],
                    'valor_unitario'     => $valorUnitario,
                    'subtotal'           => $subtotal,
                ]);

                if ($item->tipo === 'servico') {
                    $totalServicos += $subtotal;
                } else {
                    $totalProdutos += $subtotal;
                }
            }

            // ---------- RECALCULAR TOTAL FINAL ----------
            $valorTotal = $totalServicos
                + $totalProdutos
                - ($request->desconto ?? 0)
                + ($request->taxas ?? 0);

            $orcamento->update([
                'valor_total' => $valorTotal,
            ]);
        });

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