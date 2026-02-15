<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\CobrancaAnexo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContasReceberController extends Controller
{

    /**
     * Baixa múltipla de cobranças
     */
    public function pagarMultiplas(Request $request)
    {
        $ids = $request->input('cobranca_ids');
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'Nenhuma cobrança selecionada.');
        }

        $request->validate([
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,dinheiro,transferencia,cartao_credito,cartao_debito,boleto',
            'data_pagamento' => 'required|date',
        ]);

        DB::transaction(function () use ($ids, $request) {
            foreach ($ids as $id) {
                $cobranca = Cobranca::find($id);
                if (!$cobranca || $cobranca->status === 'pago') {
                    continue;
                }
                $cobranca->status = 'pago';
                $cobranca->pago_em = $request->data_pagamento;
                $cobranca->data_pagamento = $request->data_pagamento;
                $cobranca->conta_financeira_id = $request->conta_financeira_id;
                $cobranca->forma_pagamento = $request->forma_pagamento;
                $cobranca->user_id = $request->user() ? $request->user()->id : null;
                $cobranca->save();

                // Atualizar saldo da conta financeira e registrar movimentação
                $contaFinanceira = \App\Models\ContaFinanceira::find($request->conta_financeira_id);
                if ($contaFinanceira) {
                    $contaFinanceira->increment('saldo', $cobranca->valor);
                    \App\Models\MovimentacaoFinanceira::create([
                        'conta_origem_id' => null,
                        'conta_destino_id' => $request->conta_financeira_id,
                        'tipo' => 'entrada',
                        'valor' => $cobranca->valor,
                        'saldo_resultante' => $contaFinanceira->saldo,
                        'observacao' => 'Recebimento de cobrança ID ' . $cobranca->id,
                        'user_id' => $request->user() ? $request->user()->id : null,
                        'data_movimentacao' => $request->data_pagamento,
                    ]);
                }
            }
        });

        return redirect()->route('financeiro.contasareceber')->with('success', 'Cobranças baixadas com sucesso!');
    }
    public function index(Request $request)
    {
        // ================= PREPARAÇÃO DA QUERY BASE =================
        $query = Cobranca::with([
            'cliente:id,nome,nome_fantasia,cpf_cnpj',
            'anexos',
            'orcamento.empresa:id,nome_fantasia',
            'contaFixa.empresa:id,nome_fantasia'
        ])
            ->select('cobrancas.*')
            ->join('clientes', 'clientes.id', '=', 'cobrancas.cliente_id')
            ->where('cobrancas.status', '!=', 'pago');

        // Filtro por Cliente (autocomplete)
        if ($request->filled('cliente_id')) {
            $query->where('cobrancas.cliente_id', $request->input('cliente_id'));
        }
        /** @var User $user */
        $user = Auth::user();

        // Sua lógica de segurança original foi mantida.
        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );


        // ================= APLICAÇÃO DOS FILTROS =================

        // Filtro de Busca (Cliente ou Descrição)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('clientes.nome_fantasia', 'like', $searchTerm)
                    ->orWhere('cobrancas.descricao', 'like', $searchTerm);
            });
        }

        // Filtro de Período por Vencimento (pré-carregar mês atual se não tiver filtro)
        $vencimentoInicio = $request->input('vencimento_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $vencimentoFim = $request->input('vencimento_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        if ($vencimentoInicio) {
            $query->where('data_vencimento', '>=', $vencimentoInicio);
        }
        if ($vencimentoFim) {
            $query->where('data_vencimento', '<=', $vencimentoFim);
        }

        // Filtro por Tipo
        if ($request->filled('tipo')) {
            $query->where('cobrancas.tipo', $request->input('tipo'));
        }

        // Filtro por Nota Fiscal
        if ($request->filled('nota_fiscal')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nota_fiscal', 1);
            });
        }

        // Filtro por Empresa
        if ($request->filled('empresa_id')) {
            $empresaId = $request->input('empresa_id');
            $query->where(function ($q) use ($empresaId) {
                $q->whereHas('orcamento', function ($sq) use ($empresaId) {
                    $sq->where('empresa_id', $empresaId);
                })
                    ->orWhereHas('contaFixa', function ($sq) use ($empresaId) {
                        $sq->where('empresa_id', $empresaId);
                    });
            });
        }



        // Clonar a query para os KPIs (com todos os filtros, inclusive status)
        $queryParaKPIs = clone $query;

        // Clonar a query para os contadores rápidos (antes do filtro de status)
        $queryParaContadores = clone $query;

        // ================= CÁLCULO DOS KPIs E TOTAIS (baseado na query com todos os filtros) =================
        $kpis = [
            // A Receber: status diferente de pago e vencimento futuro
            'a_receber' => (clone $queryParaKPIs)
                ->where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '>=', today())
                ->sum('valor'),
            // Recebido: status pago
            'recebido'  => (clone $queryParaKPIs)
                ->where('status', 'pago')
                ->sum('valor'),
            // Vencido: status diferente de pago e vencimento passado
            'vencido'   => (clone $queryParaKPIs)
                ->where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '<', today())
                ->sum('valor'),
            // Vence hoje: status diferente de pago e vencimento hoje
            'vence_hoje' => (clone $queryParaKPIs)
                ->where('status', '!=', 'pago')
                ->whereDate('data_vencimento', today())
                ->sum('valor'),
        ];

        // Total geral filtrado: soma de todos os valores da query filtrada
        $totalGeralFiltrado = (clone $queryParaKPIs)->sum('valor');

        // ================= DADOS PARA FILTROS RÁPIDOS (baseado na query *sem* filtro de status) =================
        $contadoresStatus = [
            'pendente' => (clone $queryParaContadores)->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today())->count(),
            'vencido'  => (clone $queryParaContadores)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->count(),
        ];

        // Contadores por tipo
        $contadoresTipo = [
            'orcamento' => (clone $queryParaContadores)->where('tipo', 'orcamento')->count(),
            'contrato'  => (clone $queryParaContadores)->where('tipo', 'contrato')->count(),
        ];

        // Contador de Nota Fiscal
        $contadoresNotaFiscal = (clone $queryParaContadores)
            ->whereHas('cliente', function ($q) {
                $q->where('nota_fiscal', 1);
            })
            ->count();

        // ================= PAGINAÇÃO =================
        $cobrancas = $query
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Buscar empresas para o filtro
        $empresas = \App\Models\Empresa::select('id', 'nome_fantasia')
            ->orderBy('nome_fantasia')
            ->get();

        // Enviando TODAS as variáveis necessárias para a view
        return view('financeiro.contasareceber', compact(
            'cobrancas',
            'kpis',
            'totalGeralFiltrado',
            'contadoresStatus',
            'contadoresTipo',
            'contadoresNotaFiscal',
            'vencimentoInicio',
            'vencimentoFim',
            'empresas'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MARCAR COMO PAGO
    |--------------------------------------------------------------------------
    */
    public function pagar(Request $request, Cobranca $cobranca)
    {
        if ($cobranca->status === 'pago') {
            return back()->with('error', 'Esta cobrança já está paga.');
        }

        // Pré-processa valores para aceitar vírgula como separador decimal
        $input = $request->all();
        if (isset($input['valor_pago'])) {
            $input['valor_pago'] = str_replace(',', '.', $input['valor_pago']);
        }
        if (isset($input['juros_multa'])) {
            $input['juros_multa'] = str_replace(',', '.', $input['juros_multa']);
        }
        if (isset($input['valor_restante'])) {
            $input['valor_restante'] = str_replace(',', '.', $input['valor_restante']);
        }
        $request->merge($input);
        $request->validate([
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,dinheiro,transferencia,cartao_credito,cartao_debito,boleto',
            'valor_pago' => 'required|numeric|min:0.01',
            'juros_multa' => 'nullable|numeric|min:0',
            'data_pagamento' => 'required|date',
            'criar_nova_cobranca' => 'nullable|boolean',
            'valor_restante' => 'nullable|numeric|min:0',
            'data_vencimento_original' => 'nullable|date',
        ]);


        // Corrige valores para aceitar vírgula como separador decimal
        $valorTotal = $request->has('valor_total') ? floatval(str_replace(',', '.', $request->valor_total)) : floatval($cobranca->valor);
        $valorPago = floatval(str_replace(',', '.', $request->valor_pago));
        $jurosMulta = floatval(str_replace(',', '.', $request->juros_multa ?? 0));

        // Corrige lógica: valor pago pode ser igual ao valor total ou ao valor total + juros/multa
        $valorTotalComJuros = $valorTotal + $jurosMulta;

        // Permitir valor pago menor que o total apenas se for gerar nova cobrança com restante
        if ($valorPago < $valorTotal && (!$request->criar_nova_cobranca || $request->valor_restante <= 0)) {
            return back()->with('error', 'O valor pago não pode ser menor que o valor da cobrança.');
        }
        if ($valorPago > $valorTotalComJuros) {
            return back()->with('error', 'O valor pago não pode ser maior que o valor total da cobrança + juros/multa.');
        }

        // Validações de valor
        if ($valorPago <= 0) {
            return back()->with('error', 'O valor pago deve ser maior que zero.');
        }

        $valorTotalComJuros = $valorTotal + $jurosMulta;

        if ($valorPago > $valorTotalComJuros) {
            return back()->with('error', 'O valor pago não pode ser maior que o valor total da cobrança + juros/multa.');
        }

        DB::transaction(function () use ($cobranca, $request, $valorPago, $valorTotal, $jurosMulta) {
            // Atualizar a cobrança atual com o valor pago
            $cobranca->status = 'pago';
            $cobranca->pago_em = $request->data_pagamento;
            $cobranca->data_pagamento = $request->data_pagamento;
            $cobranca->valor = $valorTotal;
            $cobranca->juros_multa = $jurosMulta;
            $cobranca->conta_financeira_id = $request->conta_financeira_id;
            $cobranca->forma_pagamento = $request->forma_pagamento;
            $cobranca->user_id = $request->user() ? $request->user()->id : null;
            $cobranca->save();
            \Log::info('Cobrança marcada como paga', ['id' => $cobranca->id, 'status' => $cobranca->status, 'valor_pago' => $valorPago, 'juros_multa' => $jurosMulta]);

            // Atualizar o saldo da conta financeira (RECEITA = AUMENTA O SALDO)
            $contaFinanceira = \App\Models\ContaFinanceira::find($request->conta_financeira_id);
            if ($contaFinanceira) {
                $contaFinanceira->increment('saldo', $valorPago);
                // Registrar movimentação financeira (apenas UM lançamento por recebimento)
                \App\Models\MovimentacaoFinanceira::create([
                    'conta_origem_id' => null,
                    'conta_destino_id' => $request->conta_financeira_id,
                    'tipo' => 'entrada',
                    'valor' => $valorPago,
                    'saldo_resultante' => $contaFinanceira->saldo,
                    'observacao' => 'Recebimento de cobrança ID ' . $cobranca->id . ($jurosMulta > 0 ? ' | Juros/Multa: R$ ' . number_format($jurosMulta, 2, ',', '.') : ''),
                    'user_id' => $request->user() ? $request->user()->id : null,
                    'data_movimentacao' => $request->data_pagamento,
                ]);
            }

            // Atualizar status do orçamento para 'concluido' quando todas as cobranças estiverem pagas
            if ($cobranca->orcamento_id) {
                $orcamento = $cobranca->orcamento;

                // Verifica se todas as cobranças do orçamento estão pagas
                $todasPagas = $orcamento->cobrancas()->where('status', '!=', 'pago')->count() === 0;

                if ($todasPagas && $orcamento->status === 'aguardando_pagamento') {
                    $orcamento->update(['status' => 'concluido']);
                }
            }

            // Se houver valor restante, criar nova cobrança
            if ($request->criar_nova_cobranca && $request->valor_restante > 0) {
                $valorRestante = floatval($request->valor_restante);

                // Criar nova cobrança com o valor restante
                Cobranca::create([
                    'cliente_id' => $cobranca->cliente_id,
                    'orcamento_id' => $cobranca->orcamento_id,
                    'valor' => $valorRestante,
                    'descricao' => $cobranca->descricao . ' (Restante)',
                    'data_vencimento' => $request->data_vencimento_original ?? $cobranca->data_vencimento,
                    'status' => 'pendente',
                    'forma_pagamento' => $request->forma_pagamento,
                ]);
            }
        });

        $mensagem = 'Cobrança marcada como paga e registrada na movimentação.';
        if ($request->criar_nova_cobranca && $request->valor_restante > 0) {
            $mensagem .= ' Uma nova cobrança foi criada com o valor restante de R$ ' . number_format($request->valor_restante, 2, ',', '.');
        }

        return back()->with('success', $mensagem);
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR COBRANÇA
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Cobranca $cobranca)
    {
        $tipoExclusao = $request->input('tipo_exclusao', 'unica');
        $dataVencimento = $request->input('data_vencimento');

        DB::transaction(function () use ($cobranca, $tipoExclusao, $dataVencimento) {
            $orcamento = $cobranca->orcamento;

            if ($tipoExclusao === 'todas_contrato' && $cobranca->conta_fixa_id) {
                // Excluir esta e todas as cobranças futuras do mesmo contrato
                $totalExcluidas = Cobranca::where('conta_fixa_id', $cobranca->conta_fixa_id)
                    ->where('status', '!=', 'pago')
                    ->where('data_vencimento', '>=', $dataVencimento)
                    ->delete();

                session()->flash('success', "{$totalExcluidas} cobrança(s) do contrato excluída(s) com sucesso.");
            } elseif ($tipoExclusao === 'todas' && $cobranca->orcamento_id) {
                // Excluir todas as cobranças pendentes do mesmo orçamento
                $totalExcluidas = Cobranca::where('orcamento_id', $cobranca->orcamento_id)
                    ->where('status', '!=', 'pago')
                    ->delete();

                session()->flash('success', "{$totalExcluidas} cobrança(s) excluída(s) com sucesso.");
            } else {
                // Excluir apenas a cobrança específica
                $cobranca->delete();
                session()->flash('success', 'Cobrança excluída com sucesso.');
            }

            // Verificar se ainda existem cobranças pendentes para o orçamento
            if ($orcamento) {
                $restantes = $orcamento->cobrancas()
                    ->where('status', '!=', 'pago')
                    ->count();

                if ($restantes === 0) {
                    $orcamento->update(['status' => 'financeiro']);
                }
            }
        });

        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | REABRIR COBRANÇA
    |--------------------------------------------------------------------------
    */

    public function reabrir(Cobranca $cobranca)
    {
        if ($cobranca->status !== 'pago') {
            return back()->with('error', 'Apenas cobranças pagas podem ser reabertas.');
        }

        DB::transaction(function () use ($cobranca) {
            // Atualizar o saldo da conta financeira (ESTORNAR RECEITA = DIMINUI O SALDO)
            if ($cobranca->conta_financeira_id) {
                $contaFinanceira = \App\Models\ContaFinanceira::find($cobranca->conta_financeira_id);
                if ($contaFinanceira) {
                    $contaFinanceira->decrement('saldo', $cobranca->valor);
                }
            }

            $cobranca->update([
                'status'  => 'pendente',
                'pago_em' => null,
            ]);
        });

        return back()->with('success', 'Cobrança reaberta com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIMENTAÇÃO
    |--------------------------------------------------------------------------
    */

    public function movimentacao(Request $request)
    {
        // ================= DADOS PARA FILTROS =================
        $centrosCusto = \App\Models\CentroCusto::where('ativo', true)->orderBy('nome')->get();
        $categorias = \App\Models\Categoria::where('ativo', true)->orderBy('nome')->get();
        $subcategorias = \App\Models\Subcategoria::where('ativo', true)->orderBy('nome')->get();
        $contasFiltro = \App\Models\Conta::where('ativo', true)->orderBy('nome')->get();
        $empresas = \App\Models\Empresa::orderBy('nome_fantasia')->get();

        // Se não houver filtro de data, usar mês atual
        if (!$request->filled('data_inicio') && !$request->filled('data_fim')) {
            $inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            $fim = Carbon::now()->endOfMonth()->format('Y-m-d');
            $request->merge(['data_inicio' => $inicio, 'data_fim' => $fim]);
        }
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // Early return: se for filtro de saída, retorna apenas movimentações financeiras do tipo 'saida'
        if ($request->filled('tipo_movimentacao') && $request->input('tipo_movimentacao') === 'saida') {
            $movimentacoesQuery = \App\Models\MovimentacaoFinanceira::with(['contaOrigem', 'contaDestino', 'usuario'])
                ->where('tipo', 'saida');
            if ($request->filled('empresa_id')) {
                $empresaId = $request->input('empresa_id');
                $movimentacoesQuery->whereHas('contaDestino', function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                });
            }
            $movimentacoesCollection = $movimentacoesQuery
                ->when($request->filled('data_inicio'), function ($query) use ($request) {
                    $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
                })
                ->when($request->filled('data_fim'), function ($query) use ($request) {
                    $query->whereDate('data_movimentacao', '<=', $request->data_fim);
                })
                ->get()
                ->map(function ($item) {
                    $item->tipo_movimentacao = 'saida';
                    $item->is_financeiro = true;
                    return $item;
                });
            // Paginação manual
            $page = $request->get('page', 1);
            $perPage = 15;
            $total = $movimentacoesCollection->count();
            $movimentacoes = new \Illuminate\Pagination\LengthAwarePaginator(
                $movimentacoesCollection->forPage($page, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $contadoresStatus = [
                'recebido' => 0,
                'pago'     => $movimentacoesCollection->count(),
            ];
            $totalEntradas = 0;
            $totalSaidas = $movimentacoesCollection->sum('valor');
            return view('financeiro.movimentacao', compact(
                'movimentacoes',
                'centrosCusto',
                'categorias',
                'subcategorias',
                'contasFiltro',
                'empresas',
                'contadoresStatus',
                'totalEntradas',
                'totalSaidas'
            ));
        }

        // ================= BUSCAR COBRANÇAS (ENTRADAS) =================
        $cobrancasQuery = Cobranca::with([
            'cliente:id,nome,nome_fantasia,razao_social,cpf_cnpj',
            'orcamento:id,empresa_id,forma_pagamento',
            'contaFixa:id,empresa_id',
            'contaFinanceira:id,nome,tipo,empresa_id'
        ])
            ->where('status', 'pago')
            ->whereNotNull('pago_em');
        // Filtro de data para cobranças
        if ($request->filled('data_inicio')) {
            $cobrancasQuery->whereDate('pago_em', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $cobrancasQuery->whereDate('pago_em', '<=', $request->data_fim);
        }
        // Filtro por empresa: igual à tela de contas a receber
        if ($request->filled('empresa_id')) {
            $empresaId = $request->input('empresa_id');
            $cobrancasQuery->where(function ($query) use ($empresaId) {
                $query->whereHas('orcamento', function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                })
                    ->orWhereHas('contaFixa', function ($q) use ($empresaId) {
                        $q->where('empresa_id', $empresaId);
                    })
                    ->orWhereHas('contaFinanceira', function ($q) use ($empresaId) {
                        $q->where('empresa_id', $empresaId);
                    });
            });
        }
        $cobrancasPagas = $cobrancasQuery->get();
        // Montar movimentações de entrada a partir das cobranças pagas
        $movFinanceirasEntradas = $cobrancasPagas->map(function ($cobranca) {
            $mov = new \stdClass();
            $mov->tipo_movimentacao = 'entrada';
            $mov->is_financeiro = false;
            $mov->valor = $cobranca->valor;
            $mov->descricao = $cobranca->descricao;
            $mov->cliente = $cobranca->cliente;
            $mov->pago_em = $cobranca->pago_em;
            $mov->forma_pagamento = $cobranca->forma_pagamento;
            $mov->contaFinanceira = $cobranca->contaFinanceira;
            $mov->usuario = $cobranca->usuario;
            $mov->cobranca = $cobranca;
            return $mov;
        });

        // ================= BUSCAR CONTAS A PAGAR (SAÍDAS) =================
        $contasPagarQuery = \App\Models\ContaPagar::with([
            'centroCusto:id,nome',
            'conta:id,nome',
            'fornecedor:id,razao_social,nome_fantasia',
            'contaFinanceira:id,nome,tipo',
            'orcamento:id,empresa_id',
            'contaFixaPagar:id',
        ])
            ->where('status', 'pago')
            ->whereNotNull('pago_em');
        // Filtro de data para contas a pagar
        if ($request->filled('data_inicio')) {
            $contasPagarQuery->whereDate('pago_em', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $contasPagarQuery->whereDate('pago_em', '<=', $request->data_fim);
        }
        // Filtro por empresa nas despesas (contas a pagar): só mostrar despesas da empresa selecionada
        if ($request->filled('empresa_id')) {
            $empresaId = $request->input('empresa_id');
            $contasPagarQuery->where(function ($query) use ($empresaId) {
                $query->whereHas('orcamento', function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                })
                    ->orWhereHas('contaFinanceira', function ($q) use ($empresaId) {
                        $q->where('empresa_id', $empresaId);
                    });
            });
        }
        // Filtro por centro de custo: AFETA APENAS DESPESAS (CONTAS A PAGAR)
        if ($request->filled('centro_custo_id')) {
            $centroCustoId = $request->input('centro_custo_id');
            $contasPagarQuery->where('centro_custo_id', $centroCustoId);
        }
        // Filtro por centro de custo só para despesas
        if ($request->filled('centro_custo_id')) {
            $centroCustoId = $request->input('centro_custo_id');
            $contasPagarQuery->where('centro_custo_id', $centroCustoId);
        }

        // ================= FILTROS =================
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $searchRaw = $request->input('search');

            $cobrancasQuery->where(function ($q) use ($search, $searchRaw) {
                $q->whereHas(
                    'cliente',
                    fn($sq) =>
                    $sq->where('nome', 'like', $search)
                        ->orWhere('nome_fantasia', 'like', $search)
                        ->orWhere('razao_social', 'like', $search)
                )
                    ->orWhere('descricao', 'like', $search)
                    ->orWhereRaw('CAST(valor AS CHAR) LIKE ?', [$search]);
            });

            $contasPagarQuery->where(function ($q) use ($search, $searchRaw) {
                $q->where('descricao', 'like', $search)
                    ->orWhereHas('fornecedor', function ($sq) use ($search) {
                        $sq->where('razao_social', 'like', $search)
                            ->orWhere('nome_fantasia', 'like', $search);
                    })
                    ->orWhereHas('centroCusto', fn($sq) => $sq->where('nome', 'like', $search))
                    ->orWhereRaw('CAST(valor AS CHAR) LIKE ?', [$search]);
            });
        }

        if ($request->filled('data_inicio')) {
            $cobrancasQuery->whereDate('pago_em', '>=', $request->data_inicio);
            $contasPagarQuery->whereDate('pago_em', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $cobrancasQuery->whereDate('pago_em', '<=', $request->data_fim);
            $contasPagarQuery->whereDate('pago_em', '<=', $request->data_fim);
        }

        // ================= MOVIMENTAÇÕES FINANCEIRAS (AJUSTES, TRANSFERÊNCIAS, INJEÇÕES) =================
        $movFinanceirasQuery = \App\Models\MovimentacaoFinanceira::with(['contaOrigem', 'contaDestino', 'usuario']);
        // Filtros de data para movimentações financeiras
        if ($request->filled('data_inicio')) {
            $movFinanceirasQuery->whereDate('data_movimentacao', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $movFinanceirasQuery->whereDate('data_movimentacao', '<=', $request->data_fim);
        }
        // Filtro de conta
        if ($request->filled('conta_id')) {
            $movFinanceirasQuery->where(function ($q) use ($request) {
                $q->where('conta_origem_id', $request->conta_id)
                    ->orWhere('conta_destino_id', $request->conta_id);
            });
        }
        $movFinanceiras = $movFinanceirasQuery->get()
            // Filtra apenas ajustes de entrada/saída
            ->filter(function ($item) {
                return in_array($item->tipo, ['ajuste_entrada', 'ajuste_saida']);
            })
            ->map(function ($item) {
                $item->tipo_movimentacao = $item->tipo === 'ajuste_entrada' ? 'entrada' : 'saida';
                $item->is_financeiro = true;
                // Garante que contaDestino esteja sempre preenchido (objeto ou null)
                if (!isset($item->contaDestino) && isset($item->conta_destino_id) && $item->conta_destino_id) {
                    $item->contaDestino = \App\Models\ContaFinanceira::find($item->conta_destino_id);
                }
                return $item;
            });


        // ================= COMBINAR E ORDENAR =================
        // Entradas: apenas movimentações financeiras de recebimento (tipo entrada, observação 'Recebimento de cobrança ID%')
        $movFinanceirasEntradasQuery = \App\Models\MovimentacaoFinanceira::with(['contaOrigem', 'contaDestino', 'usuario'])
            ->where('tipo', 'entrada')
            ->where('observacao', 'like', 'Recebimento de cobrança ID%');
        if ($request->filled('empresa_id')) {
            $empresaId = $request->input('empresa_id');
            $movFinanceirasEntradasQuery->whereHas('contaDestino', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            });
        }
        $movFinanceirasEntradas = $movFinanceirasEntradasQuery
            ->when($request->filled('data_inicio'), function ($query) use ($request) {
                $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
            })
            ->when($request->filled('data_fim'), function ($query) use ($request) {
                $query->whereDate('data_movimentacao', '<=', $request->data_fim);
            })
            ->get()
            ->map(function ($item) {
                $item->tipo_movimentacao = 'entrada';
                $item->is_financeiro = true;
                return $item;
            });

        $contasPagar = $contasPagarQuery->get()->map(function ($item) {
            $item->tipo_movimentacao = 'saida';
            $item->is_financeiro = false;
            // Garante que usuario seja sempre o objeto User
            if (method_exists($item, 'usuario') && $item->usuario) {
                $item->usuario = $item->usuario;
            } elseif (isset($item->user_id)) {
                $item->usuario = \App\Models\User::find($item->user_id);
            } else {
                $item->usuario = null;
            }
            return $item;
        });

        $movFinanceirasSaidas = $movFinanceiras->map(function ($item) {
            // Garante que usuario seja sempre o objeto User
            if (!isset($item->usuario) && isset($item->user_id)) {
                $item->usuario = \App\Models\User::find($item->user_id);
            }
            return $item;
        });

        if ($request->filled('centro_custo_id')) {
            // Quando filtrar por centro de custo, mostrar apenas contas a pagar desse centro de custo
            $contasPagarCollection = $contasPagarQuery->get()->map(function ($item) {
                $item->tipo_movimentacao = 'saida';
                $item->is_financeiro = false;
                return $item;
            });
            // Paginação manual
            $page = $request->get('page', 1);
            $perPage = 15;
            $total = $contasPagarCollection->count();
            $movimentacoes = new \Illuminate\Pagination\LengthAwarePaginator(
                $contasPagarCollection->forPage($page, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $contadoresStatus = [
                'recebido' => 0,
                'pago'     => $contasPagarCollection->count(),
            ];
            $totalEntradas = 0;
            $totalSaidas = $contasPagarCollection->sum('valor');
            return view('financeiro.movimentacao', compact(
                'movimentacoes',
                'centrosCusto',
                'categorias',
                'subcategorias',
                'contasFiltro',
                'empresas',
                'contadoresStatus',
                'totalEntradas',
                'totalSaidas'
            ));
        } else {
            // Caso contrário, mostrar entradas e saídas corretamente
            $orderBy = $request->input('order_by', 'data');
            $orderDir = strtolower($request->input('order_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $movimentacoes = $movFinanceirasEntradas
                ->concat($contasPagar)
                ->concat($movFinanceirasSaidas)
                ->filter(function ($item) use ($request) {
                    if (!$request->filled('tipo_movimentacao')) {
                        return true;
                    }
                    return $item->tipo_movimentacao === $request->input('tipo_movimentacao');
                });

            if ($orderBy === 'data') {
                $movimentacoes = $movimentacoes->sortBy(function ($item) {
                    // Usa data_movimentacao para movimentações financeiras, pago_em para cobranças/contas a pagar
                    return $item->is_financeiro ? $item->data_movimentacao : ($item->pago_em ?? $item->data_movimentacao);
                }, SORT_REGULAR, $orderDir === 'desc');
            }

            $movimentacoes = $movimentacoes->values();

            // Paginar manualmente
            $page = $request->get('page', 1);
            $perPage = 15;
            $total = $movimentacoes->count();
            $movimentacoes = new \Illuminate\Pagination\LengthAwarePaginator(
                $movimentacoes->forPage($page, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // ================= KPIs =================
            $totalEntradas = $movFinanceirasEntradas->sum('valor') + $movFinanceirasSaidas->where('tipo_movimentacao', 'entrada')->sum('valor');
            $totalSaidas = $contasPagar->sum('valor') + $movFinanceiras->where('tipo_movimentacao', 'saida')->sum('valor');

            // ================= CONTADORES PARA FILTROS RÁPIDOS =================
            $contadoresStatus = [
                'recebido' => $movFinanceirasEntradas->count() + $movFinanceirasSaidas->where('tipo_movimentacao', 'entrada')->count(),
                'pago'     => $contasPagar->count() + $movFinanceirasSaidas->where('tipo_movimentacao', 'saida')->count(),
            ];

            return view('financeiro.movimentacao', compact(
                'movimentacoes',
                'totalEntradas',
                'totalSaidas',
                'centrosCusto',
                'categorias',
                'subcategorias',
                'contasFiltro',
                'empresas',
                'contadoresStatus'
            ));
        }
    } // ================= RECIBO =================
    public function recibo(Cobranca $cobranca)
    {
        return view('financeiro.recibos.recibo-pagamento', compact('cobranca'));
    }


    // ================= ESTORNAR PAGAMENTO =================
    public function estornar(Cobranca $cobranca)
    {
        if ($cobranca->status !== 'pago') {
            return back()->with('error', 'Apenas cobranças pagas podem ser estornadas.');
        }

        DB::transaction(function () use ($cobranca) {
            // Atualizar o saldo da conta financeira (ESTORNAR RECEITA = DIMINUI O SALDO)
            if ($cobranca->conta_financeira_id) {
                $contaFinanceira = \App\Models\ContaFinanceira::find($cobranca->conta_financeira_id);
                if ($contaFinanceira) {
                    $contaFinanceira->decrement('saldo', $cobranca->valor);
                }
            }

            // Remover movimentação financeira associada
            \App\Models\MovimentacaoFinanceira::where('tipo', 'entrada')
                ->where('observacao', 'like', 'Recebimento de cobrança ID ' . $cobranca->id . '%')
                ->delete();

            $cobranca->update([
                'status' => 'pendente',
                'pago_em' => null,
                'conta_financeira_id' => null,
                // Mantém a forma_pagamento para histórico
            ]);
        });

        return back()->with('success', 'Cobrança estornada e devolvida para Contas a Receber.');
    }

    /*
    |--------------------------------------------------------------------------
    | CONTAS FIXAS - CADASTRAR E GERAR COBRANÇAS RECORRENTES
    |--------------------------------------------------------------------------
    */
    public function storeContaFixa(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'cliente_id' => 'required|exists:clientes,id',
            'categoria' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,boleto,cartao_credito,cartao_debito,faturado',
            'periodicidade' => 'required|in:diaria,semanal,quinzenal,mensal,semestral,anual',
            'data_inicial' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicial',
            'percentual_renovacao' => 'nullable|numeric|min:0|max:100',
            'data_atualizacao_percentual' => 'nullable|date',
            'observacao' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Criar a conta fixa
            $contaFixa = \App\Models\ContaFixa::create($validated);

            // Gerar as cobranças recorrentes
            $this->gerarCobrancasRecorrentes($contaFixa);
        });

        return response()->json(['success' => true, 'message' => 'Conta fixa cadastrada com sucesso!']);
    }

    /**
     * Gera as cobranças recorrentes baseadas na conta fixa
     */
    private function gerarCobrancasRecorrentes($contaFixa)
    {
        $dataAtual = Carbon::parse($contaFixa->data_inicial);
        $dataFim = $contaFixa->data_fim ? Carbon::parse($contaFixa->data_fim) : null;
        $valorAtual = $contaFixa->valor;
        $dataProximaAtualizacao = $contaFixa->data_atualizacao_percentual
            ? Carbon::parse($contaFixa->data_atualizacao_percentual)
            : null;

        // Armazenar o dia original para tratamento de meses com dias diferentes
        $diaOriginal = $dataAtual->day;

        // Limite de segurança: gerar até 120 cobranças (10 anos para mensal)
        $limite = 120;
        $contador = 0;

        while ($contador < $limite) {
            // Se há data fim e já passou, parar
            if ($dataFim && $dataAtual->gt($dataFim)) {
                break;
            }

            // Verificar se precisa atualizar o valor pelo percentual de renovação
            if ($dataProximaAtualizacao && $contaFixa->percentual_renovacao && $dataAtual->gte($dataProximaAtualizacao)) {
                $valorAtual = $valorAtual * (1 + ($contaFixa->percentual_renovacao / 100));
                // Atualizar para a próxima data de atualização (adicionar 1 ano)
                $dataProximaAtualizacao = $dataProximaAtualizacao->copy()->addYear();
            }

            // Criar a cobrança
            $descricao = $contaFixa->categoria;
            if ($contaFixa->observacao) {
                $descricao .= " - {$contaFixa->observacao}";
            }

            Cobranca::create([
                'cliente_id' => $contaFixa->cliente_id,
                'descricao' => $descricao,
                'valor' => round($valorAtual, 2),
                'data_vencimento' => $dataAtual->format('Y-m-d'),
                'status' => 'pendente',
                'tipo' => 'contrato',
                'conta_fixa_id' => $contaFixa->id,
                'conta_financeira_id' => $contaFixa->conta_financeira_id,
                'forma_pagamento' => $contaFixa->forma_pagamento,
            ]);

            // Avançar para a próxima data baseado na periodicidade
            switch ($contaFixa->periodicidade) {
                case 'diaria':
                    $dataAtual->addDay();
                    break;
                case 'semanal':
                    $dataAtual->addWeek();
                    break;
                case 'quinzenal':
                    $dataAtual->addWeeks(2);
                    break;
                case 'mensal':
                    $dataAtual->addMonthNoOverflow();
                    // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                    if ($diaOriginal >= 30 && $dataAtual->day < $diaOriginal) {
                        $dataAtual->endOfMonth();
                    }
                    break;
                case 'semestral':
                    $dataAtual->addMonthsNoOverflow(6);
                    // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                    if ($diaOriginal >= 30 && $dataAtual->day < $diaOriginal) {
                        $dataAtual->endOfMonth();
                    }
                    break;
                case 'anual':
                    $dataAtual->addYearNoOverflow();
                    // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                    if ($diaOriginal >= 30 && $dataAtual->day < $diaOriginal) {
                        $dataAtual->endOfMonth();
                    }
                    break;
            }

            $contador++;
        }
    }

    /**
     * Retorna os dados de uma conta fixa para edição
     */
    public function getContaFixa(\App\Models\ContaFixa $contaFixa)
    {
        $contaFixa->load(['empresa', 'cliente', 'contaFinanceira']);
        return response()->json($contaFixa);
    }

    /**
     * Atualiza uma conta fixa existente
     */
    public function updateContaFixa(Request $request, \App\Models\ContaFixa $contaFixa)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'cliente_id' => 'required|exists:clientes,id',
            'categoria' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,boleto,cartao_credito,cartao_debito,faturado',
            'periodicidade' => 'required|in:diaria,semanal,quinzenal,mensal,semestral,anual',
            'data_inicial' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicial',
            'percentual_renovacao' => 'nullable|numeric|min:0|max:100',
            'data_atualizacao_percentual' => 'nullable|date',
            'observacao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($contaFixa, $validated) {
            // Atualizar a conta fixa
            $contaFixa->update($validated);

            // Atualizar todas as cobranças pendentes vinculadas a esta conta fixa
            $descricao = $validated['categoria'];
            if (!empty($validated['observacao'])) {
                $descricao .= " - {$validated['observacao']}";
            }

            Cobranca::where('conta_fixa_id', $contaFixa->id)
                ->where('status', '!=', 'pago')
                ->update([
                    'cliente_id' => $validated['cliente_id'],
                    'descricao' => $descricao,
                    'valor' => $validated['valor'],
                    'conta_financeira_id' => $validated['conta_financeira_id'],
                    'forma_pagamento' => $validated['forma_pagamento'],
                ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Conta fixa e cobranças pendentes atualizadas com sucesso!',
            'reload' => true
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GERENCIAMENTO DE ANEXOS
    |--------------------------------------------------------------------------
    */

    /**
     * Upload de anexos (NF ou Boleto)
     */
    public function uploadAnexo(Request $request, Cobranca $cobranca)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'arquivos' => 'required|array|min:1|max:5', // máximo 5 arquivos por vez
            'arquivos.*' => 'required|file|mimes:pdf|max:10240', // max 10MB por arquivo
            'tipo' => 'required|in:nf,boleto',
        ]);

        $anexosSalvos = [];
        $tamanhoTotalMB = 0;

        try {
            foreach ($request->file('arquivos') as $arquivo) {
                $nomeOriginal = $arquivo->getClientOriginalName();
                $tamanho = $arquivo->getSize();

                // Limitar tamanho total de upload (50MB no total)
                $tamanhoTotalMB += $tamanho / 1048576;
                if ($tamanhoTotalMB > 50) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tamanho total dos arquivos excede 50MB.',
                    ], 400);
                }

                // Sanitizar nome do arquivo (remover caracteres especiais)
                $nomeOriginalSanitizado = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nomeOriginal);

                // Gerar nome único para o arquivo
                $nomeArquivo = time() . '_' . uniqid() . '_' . $nomeOriginalSanitizado;
                
                // Garantir que o diretório exista no disco público
                $diretorio = 'cobrancas/anexos';
                if (!Storage::disk('public')->exists($diretorio)) {
                    Storage::disk('public')->makeDirectory($diretorio);
                }

                // Salvar arquivo usando o Storage facade
                $conteudo = file_get_contents($arquivo->getPathname());
                $caminhoCompleto = $diretorio . '/' . $nomeArquivo;

                if (!Storage::disk('public')->put($caminhoCompleto, $conteudo)) {
                    throw new \Exception('Falha ao salvar arquivo: ' . $nomeOriginal);
                }
                
                $caminho = $caminhoCompleto;

                // Criar registro no banco
                $anexo = CobrancaAnexo::create([
                    'cobranca_id' => $cobranca->id,
                    'tipo' => $request->tipo,
                    'nome_original' => $nomeOriginal,
                    'nome_arquivo' => $nomeArquivo,
                    'caminho' => $caminho,
                    'tamanho' => $tamanho,
                ]);

                $anexosSalvos[] = $anexo;
            }

            return response()->json([
                'success' => true,
                'message' => count($anexosSalvos) . ' arquivo(s) anexado(s) com sucesso!',
                'anexos' => $anexosSalvos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar anexos de uma cobrança
     */
    public function listarAnexos(Cobranca $cobranca)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $anexos = $cobranca->anexos()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'anexos' => $anexos,
        ]);
    }

    /**
     * Download de anexo
     */
    public function downloadAnexo(CobrancaAnexo $anexo)
    {
        /** @var User $user */
        $user = Auth::user();

        // Carregar relacionamento de cobrança para validação
        $anexo->load('cobranca');

        // Verificar permissão (financeiro ou cliente da cobrança)
        $isFinanceiro = $user->isAdminPanel() || $user->perfis()->where('slug', 'financeiro')->exists();

        // Para clientes: verificar se a cobrança pertence a alguma unidade dele
        $isClienteProprietario = false;
        if ($user->tipo === 'cliente') {
            $clienteIds = $user->clientes()->pluck('clientes.id');
            $isClienteProprietario = $clienteIds->contains($anexo->cobranca->cliente_id);
        }

        abort_if(!$isFinanceiro && !$isClienteProprietario, 403, 'Acesso não autorizado');

        if (empty($anexo->caminho)) {
            abort(404, 'Arquivo não disponível (upload incompleto ou arquivo perdido)');
        }

        // Usar o disco 'public' configurado para data/uploads
        if (!Storage::disk('public')->exists($anexo->caminho)) {
            abort(404, 'Arquivo não encontrado no servidor: ' . $anexo->nome_original);
        }

        return Storage::disk('public')->download($anexo->caminho, $anexo->nome_original);
    }

    /**
     * Excluir anexo
     */
    public function excluirAnexo(CobrancaAnexo $anexo)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        try {
            // Remover arquivo físico de forma segura
            $caminhoCompleto = storage_path('app/public/' . $anexo->caminho);
            if (file_exists($caminhoCompleto)) {
                if (!@unlink($caminhoCompleto)) {
                    Log::warning('Não foi possível excluir o arquivo físico: ' . $caminhoCompleto);
                }
            }

            // Remover registro do banco
            $anexo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Anexo excluído com sucesso!',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir anexo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir anexo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
