<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\CobrancaAnexo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $query = Cobranca::with(['cliente:id,nome,nome_fantasia', 'anexos', 'orcamento.empresa:id,nome_fantasia'])
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

        // Filtro por Empresa
        if ($request->filled('empresa_id')) {
            $query->whereHas('orcamento', function ($q) use ($request) {
                $q->where('empresa_id', $request->input('empresa_id'));
            });
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
        // KPIs devem considerar o período filtrado
        $kpisQueryBase = Cobranca::query();

        // Aplicar mesmo filtro de período dos KPIs
        if ($vencimentoInicio) {
            $kpisQueryBase->where('data_vencimento', '>=', $vencimentoInicio);
        }
        if ($vencimentoFim) {
            $kpisQueryBase->where('data_vencimento', '<=', $vencimentoFim);
        }

        $kpis = [
            'a_receber' => (clone $kpisQueryBase)->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today())->sum('valor'),
            'recebido'  => (clone $kpisQueryBase)->where('status', 'pago')->sum('valor'),
            'vencido'   => (clone $kpisQueryBase)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->sum('valor'),
            'vence_hoje' => (clone $kpisQueryBase)->where('status', '!=', 'pago')->whereDate('data_vencimento', today())->sum('valor'),
        ];

        $totalGeralFiltrado = (clone $kpisQueryBase)->sum('valor');

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

        $request->validate([
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,dinheiro,transferencia,cartao_credito,cartao_debito,boleto',
            'valor_pago' => 'required|numeric|min:0.01',
            'criar_nova_cobranca' => 'nullable|boolean',
            'valor_restante' => 'nullable|numeric|min:0',
            'data_vencimento_original' => 'nullable|date',
        ]);

        $valorPago = floatval($request->valor_pago);
        $valorTotal = floatval($cobranca->valor);

        // Validações de valor
        if ($valorPago <= 0) {
            return back()->with('error', 'O valor pago deve ser maior que zero.');
        }

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

        // ================= BUSCAR COBRANÇAS (ENTRADAS) =================
        $cobrancasQuery = Cobranca::with([
            'cliente:id,nome,nome_fantasia,razao_social,cpf_cnpj',
            'orcamento:id,forma_pagamento',
            'contaFinanceira:id,nome,tipo'
        ])
            ->where('status', 'pago')
            ->whereNotNull('pago_em');

        // ================= BUSCAR CONTAS A PAGAR (SAÍDAS) =================
        $contasPagarQuery = \App\Models\ContaPagar::with([
            'centroCusto:id,nome',
            'conta:id,nome',
            'fornecedor:id,razao_social,nome_fantasia',
            'contaFinanceira:id,nome,tipo'
        ])
            ->where('status', 'pago')
            ->whereNotNull('pago_em');

        // ================= FILTROS =================
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';

            $cobrancasQuery->where(function ($q) use ($search) {
                $q->whereHas(
                    'cliente',
                    fn($sq) =>
                    $sq->where('nome', 'like', $search)
                        ->orWhere('nome_fantasia', 'like', $search)
                        ->orWhere('razao_social', 'like', $search)
                )
                    ->orWhere('descricao', 'like', $search);
            });

            $contasPagarQuery->where(function ($q) use ($search) {
                $q->where('descricao', 'like', $search)
                    ->orWhereHas('fornecedor', fn($sq) => $sq->where('nome', 'like', $search))
                    ->orWhereHas('centroCusto', fn($sq) => $sq->where('nome', 'like', $search));
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

        // ================= COMBINAR E ORDENAR =================
        $cobrancas = $cobrancasQuery->get()->map(function ($item) {
            $item->tipo_movimentacao = 'entrada';
            return $item;
        });

        $contasPagar = $contasPagarQuery->get()->map(function ($item) {
            $item->tipo_movimentacao = 'saida';
            return $item;
        });

        $movimentacoes = $cobrancas->concat($contasPagar)
            ->sortByDesc('pago_em')
            ->values();

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
        $totalEntradas = $cobrancas->sum('valor');
        $totalSaidas = $contasPagar->sum('valor');

        return view('financeiro.movimentacao', compact(
            'movimentacoes',
            'totalEntradas',
            'totalSaidas'
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

                // Salvar arquivo em storage/app/public/cobrancas/anexos
                $caminho = $arquivo->storeAs('cobrancas/anexos', $nomeArquivo, 'public');

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

        $caminhoCompleto = storage_path('app/public/' . $anexo->caminho);

        if (!file_exists($caminhoCompleto)) {
            abort(404, 'Arquivo não encontrado');
        }

        return response()->download($caminhoCompleto, $anexo->nome_original);
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
