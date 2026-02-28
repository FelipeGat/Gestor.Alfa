<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\ItemComercial;
use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrcamentoController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
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

        if (! in_array($sort, $sortable)) {
            $sort = 'created_at';
        }

        // ================= QUERY BASE =================
        $query = Orcamento::with(['empresa:id,nome_fantasia', 'cliente:id,nome,nome_fantasia,razao_social', 'preCliente:id,nome_fantasia,razao_social']);

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
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $statusArray = is_array($statusFilter) ? $statusFilter : [$statusFilter];
            $statusArray = array_filter($statusArray);
            if (! empty($statusArray)) {
                $query->whereIn('status', $statusArray);
            }
        } else {
            $query->whereNotIn('status', ['recusado', 'cancelado', 'concluido']);
        }

        // ================= FILTRO POR EMPRESA (MÚLTIPLA) =================
        $empresaFilter = $request->input('empresa_id');
        if ($empresaFilter) {
            $empresaArray = is_array($empresaFilter) ? $empresaFilter : [$empresaFilter];
            $empresaArray = array_filter($empresaArray);
            if (! empty($empresaArray)) {
                $query->whereIn('empresa_id', $empresaArray);
            }
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
                        $hoje->endOfWeek(),
                    ]);
                    break;

                case 'dia':
                    $query->whereDate('created_at', $hoje->toDateString());
                    break;

                case 'intervalo':
                    if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                        $query->whereBetween('created_at', [
                            $request->data_inicio.' 00:00:00',
                            $request->data_fim.' 23:59:59',
                        ]);
                    }
                    break;
            }
        }

        // ================= ORÇAMENTOS (PAGINADO) =================
        $resumo = (clone $query)
            ->selectRaw('COUNT(*) as total_orcamentos')
            ->selectRaw("SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as aprovados")
            ->selectRaw("SUM(CASE WHEN status IN ('em_elaboracao', 'aguardando_aprovacao') THEN 1 ELSE 0 END) as pendentes")
            ->selectRaw('SUM(valor_total) as valor_total')
            ->first();

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
        $funcionariosTecnicos = Funcionario::where('ativo', true)->orderBy('nome')->get(['id', 'nome']);

        $statusList = [
            'em_elaboracao' => 'Em Elaboração',
            'aguardando_aprovacao' => 'Aguardando Aprovação',
            'aprovado' => 'Aprovado',
            'aguardando_pagamento' => 'Aguardando Pagamento',
            'agendado' => 'Agendado',
            'recusado' => 'Recusado',
            'em_andamento' => 'Em Andamento',
            'financeiro' => 'Financeiro',
            'concluido' => 'Concluído',
            'garantia' => 'Garantia',
            'cancelado' => 'Cancelado',
        ];

        return view('orcamentos.index', compact(
            'orcamentos',
            'atendimentosParaOrcamento',
            'empresas',
            'funcionariosTecnicos',
            'statusList',
            'resumo'
        ));
    }

    public function show(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        // Carrega relacionamentos
        $orcamento->load([
            'empresa',
            'cliente',
            'preCliente',
            'atendimento',
            'criadoPor',
            'itens.item',
            'taxasItens',
            'pagamentos',
            'cobrancas',
        ]);

        return view('orcamentos.show', compact('orcamento'));
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $empresas = Empresa::orderBy('nome_fantasia')->get();
        $clientes = Cliente::orderBy('nome')->get();
        $vendedores = User::query()
            ->whereNotNull('funcionario_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        $atendimento = null;

        if ($request->filled('atendimento_id')) {
            $atendimento = Atendimento::with(['empresa', 'cliente'])
                ->where('id', $request->atendimento_id)
                ->where('status_atual', 'orcamento')
                ->firstOrFail();
        }

        return view(
            'orcamentos.create',
            compact('empresas', 'clientes', 'atendimento', 'vendedores')
        );
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user->isAdminPanel() && $user->tipo !== 'comercial', 403, 'Acesso não autorizado');

        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'descricao' => 'required|string|max:255',
            'validade' => 'nullable|date|after_or_equal:today',
            'cliente_tipo' => 'required|in:cliente,pre_cliente',
            'cliente_id' => 'nullable|exists:clientes,id',
            'pre_cliente_id' => 'nullable|exists:pre_clientes,id',
            'origem_lead' => 'nullable|string|max:120',
            'probabilidade_fechamento' => 'nullable|numeric|min:0|max:100',
            'vendedor_id' => [
                'required',
                Rule::exists('users', 'id')->whereNotNull('funcionario_id'),
            ],
            'itens' => 'required|array|min:1|max:50',
            'desconto_servico_valor' => 'nullable|numeric|min:0|max:99999999',
            'desconto_produto_valor' => 'nullable|numeric|min:0|max:99999999',
            'forma_pagamento' => 'nullable|string|max:255',
            'prazo_pagamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string|max:5000',
        ]);

        if (
            ($request->cliente_tipo === 'cliente' && ! $request->filled('cliente_id')) ||
            ($request->cliente_tipo === 'pre_cliente' && ! $request->filled('pre_cliente_id'))
        ) {
            return back()
                ->withErrors(['cliente_nome' => 'Selecione um cliente ou pré-cliente válido.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $user) {

            if (! is_array($request->itens) || count($request->itens) === 0) {
                return back()
                    ->withErrors(['itens' => 'Adicione pelo menos um serviço ou produto.'])
                    ->withInput();
            }

            // ================= TAXAS =================
            $taxasLista = [];
            $totalTaxasCalculado = 0;

            if ($request->has('taxas_detalhe') && is_array($request->taxas_detalhe)) {
                foreach ($request->taxas_detalhe as $itemTaxa) {
                    $nome = $itemTaxa['nome'] ?? '';
                    $valor = (float) str_replace(',', '.', $itemTaxa['valor'] ?? 0);

                    if (! empty($nome) && $valor > 0) {
                        $taxasLista[] = ['nome' => $nome, 'valor' => $valor];
                        $totalTaxasCalculado += $valor;
                    }
                }
            }

            $clienteId = ($request->cliente_tipo === 'cliente') ? $request->cliente_id : null;
            $preClienteId = ($request->cliente_tipo === 'pre_cliente') ? $request->pre_cliente_id : null;

            // ================= CRIA ORÇAMENTO (SEM TOTAL AINDA) =================
            $orcamento = Orcamento::create([
                'empresa_id' => $request->empresa_id,
                'atendimento_id' => $request->atendimento_id,
                'numero_orcamento' => Orcamento::gerarNumero($request->empresa_id),
                'descricao' => $request->descricao,
                'validade' => $request->validade,
                'status' => 'em_elaboracao',
                'cliente_id' => $clienteId,
                'pre_cliente_id' => $preClienteId,
                'origem_lead' => $request->origem_lead,
                'probabilidade_fechamento' => $request->probabilidade_fechamento ?? 0,

                'desconto_servico_valor' => $request->desconto_servico_valor ?? 0,
                'desconto_servico_tipo' => $request->desconto_servico_tipo ?? 'valor',
                'desconto_produto_valor' => $request->desconto_produto_valor ?? 0,
                'desconto_produto_tipo' => $request->desconto_produto_tipo ?? 'valor',

                'taxas' => $totalTaxasCalculado,
                'descricao_taxas' => json_encode($taxasLista),
                'forma_pagamento' => $request->forma_pagamento,
                'prazo_pagamento' => $request->prazo_pagamento,
                'observacoes' => $request->observacoes,
                'created_by' => $user->id,
                'vendedor_id' => $request->vendedor_id,
            ]);

            // ================= ITENS =================
            $totalServicos = 0;
            $totalProdutos = 0;

            foreach ($request->itens as $itemData) {
                $itemId = $itemData['item_comercial_id'] ?? null;
                $qtd = $itemData['quantidade'] ?? null;
                $valor = $itemData['valor_unitario'] ?? null;

                if (empty($itemId) || $qtd === null || $valor === null) {
                    throw new \Exception('Item inválido no orçamento.');
                }

                $item = ItemComercial::findOrFail($itemId);

                $valorUnitario = (float) $valor;
                $quantidade = (int) $qtd;

                // Validações mais rigorosas
                if ($quantidade < 1 || $quantidade > 9999) {
                    throw new \Exception('Quantidade deve estar entre 1 e 9999.');
                }

                if ($valorUnitario < 0 || $valorUnitario > 99999999) {
                    throw new \Exception('Valor unitário inválido.');
                }

                $subtotal = $quantidade * $valorUnitario;

                OrcamentoItem::create([
                    'orcamento_id' => $orcamento->id,
                    'item_comercial_id' => $item->id,
                    'tipo' => $item->tipo,
                    'nome' => $item->nome,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'subtotal' => $subtotal,
                ]);

                if ($item->tipo === 'servico') {
                    $totalServicos += $subtotal;
                } else {
                    $totalProdutos += $subtotal;
                }
            }

            // ================= DESCONTOS REAIS =================

            // Serviços
            $descontoServicos = 0;
            if ($request->desconto_servico_valor > 0) {
                if ($request->desconto_servico_tipo === 'percentual') {
                    $descontoServicos = ($totalServicos * $request->desconto_servico_valor) / 100;
                } else {
                    $descontoServicos = (float) $request->desconto_servico_valor;
                }
            }

            // Produtos
            $descontoProdutos = 0;
            if ($request->desconto_produto_valor > 0) {
                if ($request->desconto_produto_tipo === 'percentual') {
                    $descontoProdutos = ($totalProdutos * $request->desconto_produto_valor) / 100;
                } else {
                    $descontoProdutos = (float) $request->desconto_produto_valor;
                }
            }

            $descontoTotalCalculado = $descontoServicos + $descontoProdutos;

            // ================= TRAVAS DE DESCONTO =================

            // Não permitir desconto maior que o total da categoria
            if ($descontoServicos > $totalServicos) {
                throw new \Exception('O desconto em serviços não pode ser maior que o total de serviços.');
            }

            if ($descontoProdutos > $totalProdutos) {
                throw new \Exception('O desconto em produtos não pode ser maior que o total de produtos.');
            }

            // Não permitir total negativo
            if (($totalServicos + $totalProdutos - $descontoTotalCalculado + $totalTaxasCalculado) < 0) {
                throw new \Exception('O valor final do orçamento não pode ser negativo.');
            }

            // ================= TOTAL FINAL =================
            $valorFinal = $totalServicos
                + $totalProdutos
                - $descontoTotalCalculado
                + $totalTaxasCalculado;

            $orcamento->update([
                'desconto' => $descontoTotalCalculado,
                'valor_total' => $valorFinal,
            ]);
        });

        return redirect()->route('orcamentos.index')->with('success', 'Orçamento criado com sucesso!');
    }

    public function edit(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403
        );

        $empresas = Empresa::orderBy('nome_fantasia')->get();
        $vendedores = User::query()
            ->whereNotNull('funcionario_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        $orcamento->load([
            'cliente',
            'preCliente',
            'itens' => function ($q) {
                $q->orderBy('id');
            },
        ]);

        // ================= ITENS PARA O JS =================
        $itensArray = $orcamento->itens->map(function ($item) {
            return [
                'id' => $item->item_comercial_id,
                'nome' => $item->nome,
                'tipo' => $item->tipo,
                'preco_venda' => (float) $item->valor_unitario,
                'quantidade' => (int) $item->quantidade,
            ];
        })->values();

        // ================= EXTRAS =================
        $extras = [
            'desconto' => (float) $orcamento->desconto,
            'taxas' => (float) $orcamento->taxas,
        ];

        return view(
            'orcamentos.edit',
            compact('orcamento', 'empresas', 'itensArray', 'extras', 'vendedores')
        );
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user->isAdminPanel() && $user->tipo !== 'comercial', 403, 'Acesso não autorizado');

        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'descricao' => 'required|string|max:255',
            'origem_lead' => 'nullable|string|max:120',
            'probabilidade_fechamento' => 'nullable|numeric|min:0|max:100',
            'vendedor_id' => [
                'required',
                Rule::exists('users', 'id')->whereNotNull('funcionario_id'),
            ],
            'itens' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $orcamento) {
            // Desconto Total (calculado pelo JS)
            $descontoTotal = (float) str_replace(',', '.', $request->input('desconto', 0));

            // Taxas
            $taxasLista = [];
            $totalTaxasCalculado = 0;
            if ($request->has('taxas_detalhe') && is_array($request->taxas_detalhe)) {
                foreach ($request->taxas_detalhe as $itemTaxa) {
                    $nome = $itemTaxa['nome'] ?? '';
                    $valor = (float) str_replace(',', '.', $itemTaxa['valor'] ?? 0);
                    if (! empty($nome) && $valor > 0) {
                        $taxasLista[] = ['nome' => $nome, 'valor' => $valor];
                        $totalTaxasCalculado += $valor;
                    }
                }
            }

            $clienteId = ($request->cliente_tipo === 'cliente') ? $request->cliente_id : null;
            $preClienteId = ($request->cliente_tipo === 'pre_cliente') ? $request->pre_cliente_id : null;

            $orcamento->update([
                'empresa_id' => $request->empresa_id,
                'descricao' => $request->descricao,
                'validade' => $request->validade,
                'cliente_id' => $clienteId,
                'pre_cliente_id' => $preClienteId,
                'origem_lead' => $request->origem_lead,
                'probabilidade_fechamento' => $request->probabilidade_fechamento ?? $orcamento->probabilidade_fechamento,
                'vendedor_id' => $request->vendedor_id,

                // Salvando descontos por categoria
                'desconto_servico_valor' => $request->desconto_servico_valor ?? 0,
                'desconto_servico_tipo' => $request->desconto_servico_tipo ?? 'valor',
                'desconto_produto_valor' => $request->desconto_produto_valor ?? 0,
                'desconto_produto_tipo' => $request->desconto_produto_tipo ?? 'valor',

                'desconto' => $descontoTotal,
                'taxas' => $totalTaxasCalculado,
                'descricao_taxas' => json_encode($taxasLista),
                'forma_pagamento' => $request->forma_pagamento,
                'prazo_pagamento' => $request->prazo_pagamento,
                'observacoes' => $request->observacoes,
            ]);

            $orcamento->itens()->delete();

            $totalServicos = 0;
            $totalProdutos = 0;
            foreach ($request->itens as $itemData) {
                $item = ItemComercial::findOrFail($itemData['item_comercial_id']);
                $valorUnitario = (float) $itemData['valor_unitario'];
                $subtotal = $itemData['quantidade'] * $valorUnitario;

                OrcamentoItem::create([
                    'orcamento_id' => $orcamento->id,
                    'item_comercial_id' => $item->id,
                    'tipo' => $item->tipo,
                    'nome' => $item->nome,
                    'quantidade' => $itemData['quantidade'],
                    'valor_unitario' => $valorUnitario,
                    'subtotal' => $subtotal,
                ]);

                if ($item->tipo === 'servico') {
                    $totalServicos += $subtotal;
                } else {
                    $totalProdutos += $subtotal;
                }
            }

            // Desconto Serviços
            $descontoServicos = 0;
            if ($request->desconto_servico_valor > 0) {
                if ($request->desconto_servico_tipo === 'percentual') {
                    $descontoServicos = ($totalServicos * $request->desconto_servico_valor) / 100;
                } else {
                    $descontoServicos = (float) $request->desconto_servico_valor;
                }
            }

            // Desconto Produtos
            $descontoProdutos = 0;
            if ($request->desconto_produto_valor > 0) {
                if ($request->desconto_produto_tipo === 'percentual') {
                    $descontoProdutos = ($totalProdutos * $request->desconto_produto_valor) / 100;
                } else {
                    $descontoProdutos = (float) $request->desconto_produto_valor;
                }
            }

            $descontoTotalCalculado = $descontoServicos + $descontoProdutos;

            // ================= TOTAL FINAL =================
            $valorFinal = $totalServicos
                + $totalProdutos
                - $descontoTotalCalculado
                + $totalTaxasCalculado;

            // ================= TRAVAS DE DESCONTO =================
            if ($descontoServicos > $totalServicos) {
                throw new \Exception('O desconto em serviços não pode ser maior que o total de serviços.');
            }

            if ($descontoProdutos > $totalProdutos) {
                throw new \Exception('O desconto em produtos não pode ser maior que o total de produtos.');
            }

            if ($valorFinal < 0) {
                throw new \Exception('O valor final do orçamento não pode ser negativo.');
            }

            $orcamento->update([
                'desconto' => $descontoTotalCalculado,
                'valor_total' => $valorFinal,
            ]);
        });

        return redirect()->route('orcamentos.index')->with('success', 'Orçamento atualizado com sucesso!');
    }

    public function destroy(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        // TRAVA FINANCEIRA
        if (\App\Models\Cobranca::where('descricao', 'like', "%{$orcamento->numero_orcamento}%")->exists()) {
            return back()->withErrors([
                'delete' => 'Este orçamento possui cobrança vinculada. Solicite ao financeiro a exclusão da cobrança antes de remover o orçamento.',
            ]);
        }

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
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403
        );

        return response()->json([
            'numero' => Orcamento::gerarNumero((int) $empresaId),
        ]);
    }

    public function updateStatus(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        try {
            $request->validate([
                'orcamento_status' => 'required|in:em_elaboracao,aguardando_aprovacao,aprovado,financeiro,aguardando_pagamento,em_andamento,agendado,concluido,recusado,cancelado,garantia',
            ]);

            $novoStatus = $request->orcamento_status;

            if (in_array($novoStatus, ['aprovado', 'agendado'], true)) {
                return back()->with('error', 'Para este status, utilize o agendamento técnico no modal da tela de orçamentos.');
            }

            // Atualiza status do orçamento
            $dadosAtualizacao = [
                'status' => $novoStatus,
            ];

            if ($novoStatus === 'aguardando_aprovacao' && ! $orcamento->data_envio) {
                $dadosAtualizacao['data_envio'] = now();
            }

            if (
                in_array($novoStatus, ['aprovado', 'financeiro', 'aguardando_pagamento', 'em_andamento', 'agendado', 'concluido', 'garantia'])
                && ! $orcamento->data_aprovacao
            ) {
                $dadosAtualizacao['data_aprovacao'] = now();
            }

            $orcamento->update($dadosAtualizacao);

            return back()->with('success', 'Status atualizado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar status: '.$e->getMessage());
        }
    }

    public function duplicate(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user->isAdminPanel() && $user->tipo !== 'comercial', 403, 'Acesso não autorizado');

        $original = $orcamento->load(['itens', 'taxasItens', 'pagamentos']);

        $novoNumero = Orcamento::gerarNumero($original->empresa_id);

        $novoOrcamento = DB::transaction(function () use ($original, $user, $novoNumero) {
            $novoOrcamento = Orcamento::create([
                'empresa_id' => $original->empresa_id,
                'atendimento_id' => null,
                'numero_orcamento' => $novoNumero,
                'descricao' => $original->descricao,
                'validade' => $original->validade,
                'status' => 'em_elaboracao',
                'cliente_id' => $original->cliente_id,
                'pre_cliente_id' => $original->pre_cliente_id,
                'desconto_servico_valor' => $original->desconto_servico_valor,
                'desconto_servico_tipo' => $original->desconto_servico_tipo,
                'desconto_produto_valor' => $original->desconto_produto_valor,
                'desconto_produto_tipo' => $original->desconto_produto_tipo,
                'taxas' => $original->taxas,
                'descricao_taxas' => $original->descricao_taxas,
                'forma_pagamento' => $original->forma_pagamento,
                'prazo_pagamento' => $original->prazo_pagamento,
                'observacoes' => $original->observacoes,
                'created_by' => $user->id,
            ]);

            foreach ($original->itens as $item) {
                OrcamentoItem::create([
                    'orcamento_id' => $novoOrcamento->id,
                    'item_comercial_id' => $item->item_comercial_id,
                    'tipo' => $item->tipo,
                    'nome' => $item->nome,
                    'quantidade' => $item->quantidade,
                    'valor_unitario' => $item->valor_unitario,
                    'subtotal' => $item->subtotal,
                ]);
            }

            foreach ($original->taxasItens as $taxa) {
                \App\Models\OrcamentoTaxa::create([
                    'orcamento_id' => $novoOrcamento->id,
                    'nome' => $taxa->nome,
                    'valor' => $taxa->valor,
                ]);
            }

            foreach ($original->pagamentos as $pagamento) {
                \App\Models\OrcamentoPagamento::create([
                    'orcamento_id' => $novoOrcamento->id,
                    'forma' => $pagamento->forma,
                    'percentual' => $pagamento->percentual,
                    'valor' => $pagamento->valor,
                    'parcelas' => $pagamento->parcelas,
                ]);
            }

            return $novoOrcamento;
        });

        return redirect()->route('orcamentos.edit', $novoOrcamento)
            ->with('success', 'Orçamento duplicado com sucesso! Novo número: '.$novoNumero);
    }

    public function imprimir($id)
    {
        $orcamento = Orcamento::with([
            'empresa',
            'cliente',
            'cliente.emails',
            'cliente.telefones',
            'preCliente',
            'itens',
        ])->findOrFail($id);

        $view = 'orcamentos.'.$orcamento->empresa->layout_pdf;

        if (! view()->exists($view)) {
            abort(500, 'Layout de impressão não encontrado.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView($view, [
            'orcamento' => $orcamento,
            'empresa' => $orcamento->empresa,
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'orcamento_'.str_replace(
            ['/', '\\'],
            '-',
            $orcamento->numero_orcamento
        ).'.pdf';

        return $pdf->stream($filename);
    }
}
