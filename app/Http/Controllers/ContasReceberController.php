<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContasReceberController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Sua lógica de segurança original foi mantida.
        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // ================= PREPARAÇÃO DA QUERY BASE =================
        $query = Cobranca::with('cliente:id,nome,nome_fantasia')
            ->select('cobrancas.*')
            ->join('clientes', 'clientes.id', '=', 'cobrancas.cliente_id')
            ->where('cobrancas.status', '!=', 'pago');


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

        // Clonando a query *antes* do filtro de status para os contadores
        $queryParaContadores = clone $query;

        // Filtro de Status (pode ser um ou mais)
        if ($request->filled('status') && is_array($request->input('status')) && !empty($request->input('status')[0])) {
            $query->where(function ($q) use ($request) {
                foreach ($request->input('status') as $status) {
                    if ($status === 'pendente') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today());
                        });
                    } elseif ($status === 'vencido') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today());
                        });
                    }
                }
            });
        }

        // ================= CÁLCULO DOS KPIs E TOTAIS (baseado na query com filtros) =================
        $kpisQuery = (clone $query)->toBase();

        $kpis = [
            'a_receber' => (clone $kpisQuery)->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today())->sum('valor'),
            'recebido'  => (clone $kpisQuery)->where('status', 'pago')->sum('valor'),
            'vencido'   => (clone $kpisQuery)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->sum('valor'),
            'vence_hoje' => (clone $kpisQuery)->where('status', '!=', 'pago')->whereDate('data_vencimento', today())->sum('valor'),
        ];

        $totalGeralFiltrado = (clone $kpisQuery)->sum('valor');

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

        // ================= PAGINAÇÃO =================
        $cobrancas = $query
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Enviando TODAS as variáveis necessárias para a view
        return view('financeiro.contasareceber', compact(
            'cobrancas',
            'kpis',
            'totalGeralFiltrado',
            'contadoresStatus',
            'contadoresTipo',
            'vencimentoInicio',
            'vencimentoFim'
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

        $request->validate([
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,dinheiro,transferencia,cartao_credito,cartao_debito,boleto',
            'valor_pago' => 'required|numeric|min:0',
            'criar_nova_cobranca' => 'nullable|boolean',
            'valor_restante' => 'nullable|numeric|min:0',
            'data_vencimento_original' => 'nullable|date',
        ]);

        $valorPago = floatval($request->valor_pago);
        $valorTotal = floatval($cobranca->valor);

        // Validar que o valor pago não seja maior que o total
        if ($valorPago > $valorTotal) {
            return back()->with('error', 'O valor pago não pode ser maior que o valor total da cobrança.');
        }

        DB::transaction(function () use ($cobranca, $request, $valorPago, $valorTotal) {
            // Atualizar a cobrança atual com o valor pago
            $cobranca->update([
                'status' => 'pago',
                'pago_em' => now(),
                'valor' => $valorPago, // Atualiza o valor para o que foi efetivamente pago
                'conta_financeira_id' => $request->conta_financeira_id,
                'forma_pagamento' => $request->forma_pagamento,
            ]);

            // Atualizar o saldo da conta financeira (RECEITA = AUMENTA O SALDO)
            $contaFinanceira = \App\Models\ContaFinanceira::find($request->conta_financeira_id);
            if ($contaFinanceira) {
                $contaFinanceira->increment('saldo', $valorPago);
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
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // ================= QUERY BASE (APENAS PAGOS) =================
        $query = Cobranca::with([
            'cliente:id,nome,nome_fantasia,razao_social,cpf_cnpj',
            'orcamento:id,forma_pagamento',
            'contaFinanceira:id,nome,tipo'
        ])
            ->where('status', 'pago')
            ->whereNotNull('pago_em');

        // ================= FILTROS =================
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    'cliente',
                    fn($sq) =>
                    $sq->where('nome', 'like', $search)
                        ->orWhere('nome_fantasia', 'like', $search)
                        ->orWhere('razao_social', 'like', $search)
                )
                    ->orWhere('descricao', 'like', $search);
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('pago_em', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('pago_em', '<=', $request->data_fim);
        }

        // ================= KPIs =================
        $totalEntradas = (clone $query)->sum('valor');

        // ================= PAGINAÇÃO =================
        $movimentacoes = $query
            ->orderBy('pago_em', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('financeiro.movimentacao', compact(
            'movimentacoes',
            'totalEntradas'
        ));
    }

    // ================= RECIBO =================
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
                    $dataAtual->addMonth();
                    break;
                case 'semestral':
                    $dataAtual->addMonths(6);
                    break;
                case 'anual':
                    $dataAtual->addYear();
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
}
