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
use Carbon\Carbon;



class OrcamentoController extends Controller
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

        // ================= CONFIG ORDENAÇÃO =================
        $sortable = [
            'numero_orcamento',
            'status',
            'valor_total',
            'created_at',
        ];

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (!in_array($sort, $sortable)) {
            $sort = 'created_at';
        }

        // ================= QUERY BASE =================
        $query = Orcamento::with(['empresa', 'cliente']);

        // ================= COMERCIAL: LIMITA EMPRESAS =================
        if ($user->tipo === 'comercial') {
            $empresaIds = $user->empresas->pluck('id');

            if ($empresaIds->isEmpty()) {
                return view('orcamentos.index', [
                    'orcamentos' => collect(),
                    'atendimentosParaOrcamento' => collect(),
                ]);
            }

            $query->whereIn('empresa_id', $empresaIds);
        }

        // ================= FILTRO DE BUSCA =================
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // Nº Orçamento
                $q->where('numero_orcamento', 'like', "%{$search}%")

                    // Status
                    ->orWhere('status', 'like', "%{$search}%")

                    // Cliente
                    ->orWhereHas('cliente', function ($qc) use ($search) {
                        $qc->where('nome', 'like', "%{$search}%");
                    })
                    ->orWhereHas('preCliente', function ($qp) use ($search) {
                        $qp->where('nome_fantasia', 'like', "%{$search}%")
                            ->orWhere('razao_social', 'like', "%{$search}%");
                    })

                    // Empresa
                    ->orWhereHas('empresa', function ($qe) use ($search) {
                        $qe->where('nome_fantasia', 'like', "%{$search}%");
                    });
            });
        }

        // ================= FILTRO POR STATUS (MÚLTIPLO) =================
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        // ================= FILTRO POR EMPRESA (MÚLTIPLA) =================
        if ($request->filled('empresa_id')) {
            $query->whereIn('empresa_id', (array) $request->empresa_id);
        }

        // ================= FILTRO POR PERÍODO =================
        if ($request->filled('periodo')) {

            $hoje = Carbon::now();

            switch ($request->periodo) {

                case 'ano':
                    $query->whereYear('created_at', $hoje->year);
                    break;

                case 'mes':
                    $ano = $request->get('ano', now()->year);
                    $mes = $request->get('mes', now()->month);

                    $query->whereYear('created_at', $ano)
                        ->whereMonth('created_at', $mes);
                    break;

                case 'semana':
                    $query->whereBetween('created_at', [
                        $hoje->startOfWeek(),
                        $hoje->endOfWeek()
                    ]);
                    break;

                case 'dia':
                    $query->whereDate('created_at', $hoje->toDateString());
                    break;

                case 'intervalo':
                    if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                        $query->whereBetween('created_at', [
                            $request->data_inicio . ' 00:00:00',
                            $request->data_fim . ' 23:59:59'
                        ]);
                    }
                    break;
            }
        }

        // ================= ORÇAMENTOS (PAGINADO) =================
        $orcamentos = $query
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

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

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('orcamentos.index', compact(
            'orcamentos',
            'atendimentosParaOrcamento',
            'empresas'
        ));
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

        if ($request->filled('atendimento_id')) {
            $atendimento = Atendimento::with(['empresa', 'cliente'])
                ->where('id', $request->atendimento_id)
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
            'cliente_id'      => 'required_if:cliente_tipo,cliente|nullable|exists:clientes,id',
            'pre_cliente_id'  => 'required_if:cliente_tipo,pre_cliente|nullable|exists:pre_clientes,id',

            'desconto'        => 'nullable|numeric|min:0',
            'taxas'           => 'nullable|numeric|min:0',
            'forma_pagamento' => 'nullable|string',

            'itens' => 'required|array|min:1',
            'itens.*.item_comercial_id' => 'required|exists:itens_comerciais,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        if ($request->filled('atendimento_id')) {
            $existe = Orcamento::where('atendimento_id', $request->atendimento_id)->exists();

            if ($existe) {
                return redirect()
                    ->route('orcamentos.index')
                    ->with('error', 'Este atendimento já possui um orçamento.');
            }
        }

        DB::transaction(function () use ($request, $user) {

            // ---------- DESCONTO / TAXAS ----------
            $desconto = (float) str_replace(',', '.', $request->input('desconto', 0));
            $taxas    = (float) str_replace(',', '.', $request->input('taxas', 0));

            // ---------- CLIENTE / PRÉ CLIENTE ----------
            $clienteId    = null;
            $preClienteId = null;

            if ($request->cliente_tipo === 'cliente') {
                $clienteId = $request->cliente_id;
            } else {
                $preClienteId = $request->pre_cliente_id;
            }

            // ---------- CRIA ORÇAMENTO ----------
            $orcamento = Orcamento::create([
                'empresa_id'       => $request->empresa_id,
                'atendimento_id'   => $request->atendimento_id,
                'numero_orcamento' => Orcamento::gerarNumero($request->empresa_id),
                'descricao'        => $request->descricao,
                'validade'         => $request->validade,
                'status'           => 'em_elaboracao',
                'cliente_id'       => $clienteId,
                'pre_cliente_id'   => $preClienteId,
                'desconto_servico_valor' => $request->desconto_servico_valor ?? 0,
                'desconto_servico_tipo'  => $request->desconto_servico_tipo ?? 'valor',
                'desconto_produto_valor' => $request->desconto_produto_valor ?? 0,
                'desconto_produto_tipo'  => $request->desconto_produto_tipo ?? 'valor',
                'desconto'          => $desconto,
                'taxas'             => $taxas,
                'forma_pagamento'   => $request->forma_pagamento,
                'observacoes'       => $request->observacoes,
                'created_by'        => $user->id,
            ]);

            // ---------- ITENS DO ORÇAMENTO ----------
            $totalServicos = 0;
            $totalProdutos = 0;

            foreach ($request->itens as $itemData) {

                $item = ItemComercial::findOrFail($itemData['item_comercial_id']);

                $valorUnitario = (float) $itemData['valor_unitario'];
                $subtotal      = $itemData['quantidade'] * $valorUnitario;

                OrcamentoItem::create([
                    'orcamento_id'      => $orcamento->id,
                    'item_comercial_id' => $item->id,
                    'tipo'              => $item->tipo,
                    'nome'              => $item->nome,
                    'quantidade'        => $itemData['quantidade'],
                    'valor_unitario'    => $valorUnitario,
                    'subtotal'          => $subtotal,
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
                - $desconto
                + $taxas;

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

        // ================= EXTRAS =================
        $extras = [
            'desconto' => (float) $orcamento->desconto,
            'taxas'    => (float) $orcamento->taxas,
        ];

        return view(
            'orcamentos.edit',
            compact('orcamento', 'empresas', 'itensArray', 'extras')
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

            $desconto = (float) str_replace(',', '.', $request->input('desconto', 0));

            $taxas = (float) str_replace(
                ',',
                '.',
                $request->input('taxas', 0)
            );

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
                'atendimento_id'   => $request->atendimento_id,
                'descricao'        => $request->descricao,
                'validade'         => $request->validade,
                'status'           => 'em_elaboracao',
                'cliente_id'       => $clienteId,
                'pre_cliente_id'   => $preClienteId,
                'desconto_servico_valor' => $request->desconto_servico_valor ?? 0,
                'desconto_servico_tipo'  => $request->desconto_servico_tipo ?? 'valor',
                'desconto_produto_valor' => $request->desconto_produto_valor ?? 0,
                'desconto_produto_tipo'  => $request->desconto_produto_tipo ?? 'valor',
                'desconto'          => $desconto,
                'taxas'             => $taxas,
                'forma_pagamento' => $request->forma_pagamento,
                'observacoes'     => $request->observacoes,
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
                - $desconto
                + $taxas;

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

    public function updateStatus(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'status' => 'required|in:em_elaboracao,aguardando_aprovacao,enviado,aprovado,recusado,concluido,garantia,cancelado,aguardando_pagamento,agendado,em_andamento,financeiro'
        ]);

        $orcamento->update([
            'status' => $request->status,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Status do orçamento atualizado com sucesso.');
    }

    public function imprimir($id)
    {
        $orcamento = Orcamento::with([
            'empresa',
            'cliente',
            'cliente.emails',
            'cliente.telefones',
            'preCliente',
            'itens'
        ])->findOrFail($id);

        $view = 'orcamentos.' . $orcamento->empresa->layout_pdf;

        if (!view()->exists($view)) {
            abort(500, 'Layout de impressão não encontrado.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView($view, [
            'orcamento' => $orcamento,
            'empresa'   => $orcamento->empresa
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'orcamento_' . str_replace(
            ['/', '\\'],
            '-',
            $orcamento->numero_orcamento
        ) . '.pdf';

        return $pdf->stream($filename);
    }
}
