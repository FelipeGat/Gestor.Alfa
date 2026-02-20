<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use App\Models\ContaFixaPagar;
use App\Models\ContaPagar;
use App\Models\ContaPagarAnexo;
use App\Models\Subcategoria;
use App\Services\Financeiro\ContaPagarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContasPagarController extends Controller
{
    protected $service;

    public function __construct(ContaPagarService $service)
    {
        $this->service = $service;
    }

    /**
     * Exibe a listagem principal das contas a pagar
     */
    public function index(Request $request)
    {

        // Monta a query base
        $query = ContaPagar::with(['fornecedor', 'centroCusto', 'conta']);

        // Filtro de busca geral
        if ($request->filled('search')) {
            $searchTerm = '%'.$request->input('search').'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('descricao', 'like', $searchTerm)
                    ->orWhereHas('fornecedor', function ($sq) use ($searchTerm) {
                        $sq->where('razao_social', 'like', $searchTerm)
                            ->orWhere('nome_fantasia', 'like', $searchTerm);
                    })
                    ->orWhereHas('centroCusto', function ($sq) use ($searchTerm) {
                        $sq->where('nome', 'like', $searchTerm);
                    });
            });
        }

        // Filtro por centro de custo
        if ($request->filled('centro_custo_id')) {
            $query->where('centro_custo_id', $request->input('centro_custo_id'));
        }

        // Filtro por categoria
        if ($request->filled('categoria_id')) {
            $query->whereHas('conta', function ($q) use ($request) {
                $q->where('categoria_id', $request->input('categoria_id'));
            });
        }

        // Filtro por subcategoria
        if ($request->filled('subcategoria_id')) {
            $query->whereHas('conta', function ($q) use ($request) {
                $q->where('subcategoria_id', $request->input('subcategoria_id'));
            });
        }

        // Filtro por conta
        if ($request->filled('conta_id')) {
            $query->where('conta_id', $request->input('conta_id'));
        }

        // Filtro por período de vencimento
        $vencimentoInicio = $request->input('vencimento_inicio') ?? now()->startOfMonth()->format('Y-m-d');
        $vencimentoFim = $request->input('vencimento_fim') ?? now()->endOfMonth()->format('Y-m-d');
        $query->whereDate('data_vencimento', '>=', $vencimentoInicio)
            ->whereDate('data_vencimento', '<=', $vencimentoFim);

        // Filtro por status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if (is_array($status)) {
                $query->where(function ($q) use ($status) {
                    foreach ($status as $s) {
                        if ($s === 'vencido') {
                            $q->orWhere(function ($sq) {
                                $sq->where('status', '!=', 'pago')
                                    ->whereDate('data_vencimento', '<', now()->toDateString());
                            });
                        } else {
                            $q->orWhere('status', $s);
                        }
                    }
                });
            } else {
                $query->where('status', $status);
            }
        } else {
            // Se não houver filtro de status, mostrar apenas contas não pagas
            $query->where('status', '!=', 'pago');
        }

        $contas = $query->orderBy('data_vencimento', 'asc')
            ->paginate(15)
            ->appends($request->except('page'));

        // KPIs - usando service com filtros
        $filtros = [
            'centro_custo_id' => $request->input('centro_custo_id'),
            'categoria_id' => $request->input('categoria_id'),
            'subcategoria_id' => $request->input('subcategoria_id'),
            'conta_id' => $request->input('conta_id'),
            'search' => $request->input('search'),
        ];

        // Só passa período se o usuário definiu explicitamente
        if ($request->filled('vencimento_inicio')) {
            $filtros['vencimento_inicio'] = $vencimentoInicio;
        }
        if ($request->filled('vencimento_fim')) {
            $filtros['vencimento_fim'] = $vencimentoFim;
        }

        $kpis = $this->service->calcularKPIs($filtros);
        $contadoresStatus = $this->service->contarPorStatus($filtros);

        // Total geral filtrado (soma dos valores das contas exibidas na página)
        $totalGeralFiltrado = $contas->sum('valor');

        $centrosCusto = \App\Models\CentroCusto::where('ativo', true)->orderBy('nome')->get();

        $categorias = \App\Models\Categoria::orderBy('nome')->get();

        $subcategorias = \App\Models\Subcategoria::orderBy('nome')->get();

        $contasFinanceiras = \App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get();

        $contasFiltro = \App\Models\Conta::orderBy('nome')->get();

        // Buscar todos os fornecedores ativos para o autocomplete (não apenas das contas paginadas)
        $fornecedores = \App\Models\Fornecedor::where('ativo', true)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'nome_fantasia']);

        // Adicione outras variáveis conforme necessário para a view
        return view('financeiro.contasapagar', compact('contas', 'centrosCusto', 'categorias', 'subcategorias', 'contasFiltro', 'contadoresStatus', 'kpis', 'totalGeralFiltrado', 'contasFinanceiras', 'fornecedores'));
    }

    /**
     * Baixa múltipla de contas a pagar
     */
    public function pagarMultiplas(Request $request)
    {
        $ids = $request->input('conta_ids');
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }
        if (! is_array($ids) || empty($ids)) {
            return back()->with('error', 'Nenhuma conta selecionada.');
        }

        $request->validate([
            'conta_financeira_id' => 'required|exists:contas_financeiras,id',
            'forma_pagamento' => 'required|in:pix,dinheiro,transferencia,cartao_credito,cartao_debito,boleto',
            'data_pagamento' => 'required|date',
        ]);

        $this->service->pagar($ids, [
            'conta_financeira_id' => $request->conta_financeira_id,
            'forma_pagamento' => $request->forma_pagamento,
            'data_pagamento' => $request->data_pagamento,
        ]);

        return redirect()->route('financeiro.contasapagar')->with('success', 'Contas pagas com sucesso!');
    }

    // Os métodos do controller devem estar aqui dentro, sem código solto fora de métodos.

    /**
     * MARCAR COMO PAGO
     */
    public function marcarComoPago(Request $request, ContaPagar $conta)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        // VALIDAÇÃO: Se for conta fixa (recorrente), verificar se há parcela anterior não paga
        if ($conta->conta_fixa_pagar_id) {
            $parcelaAnteriorNaoPaga = ContaPagar::where('conta_fixa_pagar_id', $conta->conta_fixa_pagar_id)
                ->where('status', '!=', 'pago')
                ->where('data_vencimento', '<', $conta->data_vencimento)
                ->exists();

            if ($parcelaAnteriorNaoPaga) {
                return back()->withErrors(['error' => 'Não é possível pagar esta parcela. Existe uma parcela anterior que ainda não foi paga. Para manter a consistência das despesas fixas, você deve pagar as parcelas na ordem cronológica.']);
            }
        }

        $request->validate([
            'forma_pagamento' => 'required|string|max:50',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
            'valor_pago' => 'required|numeric|min:0.01',
            'juros_multa' => 'nullable|numeric|min:0',
            'data_pagamento' => 'required|date',
            'criar_nova_conta' => 'nullable|boolean',
            'valor_restante' => 'nullable|numeric|min:0',
            'data_vencimento_original' => 'nullable|date',
        ]);

        // Usa o valor_total enviado pelo formulário, se existir, senão mantém o valor original
        $valorTotal = $request->has('valor_total') ? floatval($request->valor_total) : floatval($conta->valor);
        $valorPago = floatval($request->valor_pago);
        $jurosMulta = floatval($request->juros_multa ?? 0);

        // Validações de valor
        if ($valorPago <= 0) {
            return back()->with('error', 'O valor pago deve ser maior que zero.');
        }
        // Permite valor pago diferente do valor total, para casos de fatura variável

        DB::transaction(function () use ($conta, $request, $valorPago, $valorTotal, $jurosMulta) {
            // Atualizar a conta atual com o valor pago e o valor total ajustado
            $conta->update([
                'status' => 'pago',
                'pago_em' => $request->data_pagamento,
                'data_pagamento' => $request->data_pagamento,
                'valor' => $valorTotal, // Salva o valor ajustado para este mês
                'juros_multa' => $jurosMulta,
                'forma_pagamento' => $request->forma_pagamento,
                'conta_financeira_id' => $request->conta_financeira_id,
                'user_id' => $request->user() ? $request->user()->id : null,
            ]);

            // Atualizar saldo da conta bancária se informada (DESPESA = DIMINUI O SALDO)
            if ($request->conta_financeira_id) {
                $contaBancaria = \App\Models\ContaFinanceira::find($request->conta_financeira_id);
                if ($contaBancaria) {
                    $contaBancaria->decrement('saldo', $valorPago);
                    // Antes de criar, remove qualquer movimentação anterior deste pagamento (evita duplicidade)
                    \App\Models\MovimentacaoFinanceira::where('tipo', 'saida')
                        ->where(function ($q) use ($conta) {
                            $q->where('observacao', 'like', 'Pagamento de conta a pagar ID '.$conta->id.'%');
                        })
                        ->delete();
                    // Registrar movimentação financeira (apenas UM lançamento por pagamento)
                    \App\Models\MovimentacaoFinanceira::create([
                        'conta_origem_id' => $request->conta_financeira_id,
                        'conta_destino_id' => null,
                        'tipo' => 'saida',
                        'valor' => $valorPago, // SEMPRE o valor pago!
                        'saldo_resultante' => $contaBancaria->saldo,
                        'observacao' => 'Pagamento de conta a pagar ID '.$conta->id.' | Valor pago: R$ '.number_format($valorPago, 2, ',', '.'),
                        'user_id' => $request->user() ? $request->user()->id : null,
                        'data_movimentacao' => $request->data_pagamento,
                    ]);
                }
            }

            // Se houver valor restante, criar nova conta
            if ($request->criar_nova_conta && $request->valor_restante > 0) {
                $valorRestante = floatval($request->valor_restante);

                // Criar nova conta com o valor restante
                ContaPagar::create([
                    'centro_custo_id' => $conta->centro_custo_id,
                    'conta_id' => $conta->conta_id,
                    'fornecedor_id' => $conta->fornecedor_id,
                    'conta_fixa_pagar_id' => $conta->conta_fixa_pagar_id,
                    'descricao' => $conta->descricao.' (Restante)',
                    'valor' => $valorRestante,
                    'data_vencimento' => $request->data_vencimento_original ?? $conta->data_vencimento,
                    'status' => 'em_aberto',
                    'tipo' => $conta->tipo,
                    'user_id' => $request->user() ? $request->user()->id : null,
                ]);
            }
        });

        $mensagem = 'Conta marcada como paga e registrada na movimentação.';
        if ($request->criar_nova_conta && $request->valor_restante > 0) {
            $mensagem .= ' Uma nova conta foi criada com o valor restante de R$ '.number_format($request->valor_restante, 2, ',', '.');
        }

        return back()->with('success', $mensagem);
    }

    /**
     * ESTORNAR PAGAMENTO
     */
    public function estornar(ContaPagar $conta)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        if ($conta->status !== 'pago') {
            return back()->with('error', 'Apenas contas pagas podem ser estornadas.');
        }

        DB::transaction(function () use ($conta) {
            // Estornar saldo da conta bancária (devolver o dinheiro)
            if ($conta->conta_financeira_id) {
                $contaFinanceira = \App\Models\ContaFinanceira::find($conta->conta_financeira_id);
                if ($contaFinanceira) {
                    $contaFinanceira->increment('saldo', $conta->valor);
                }
            }

            // Remover TODAS as movimentações financeiras associadas a este pagamento
            \App\Models\MovimentacaoFinanceira::where('tipo', 'saida')
                ->where(function ($q) use ($conta) {
                    $q->where('observacao', 'like', 'Pagamento de conta a pagar ID '.$conta->id.'%');
                })
                ->delete();

            $conta->update([
                'status' => 'em_aberto',
                'pago_em' => null,
                'conta_financeira_id' => null,
            ]);
        });

        return redirect()->route('financeiro.contasapagar')->with('success', 'Pagamento estornado e devolvido para Contas a Pagar.');
    }

    /**
     * CRIAR CONTA AVULSA
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id' => 'required|exists:centros_custo,id',
            'conta_id' => 'required|exists:contas,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01|max:999999.99',
            'data_vencimento' => 'required|date',
            'observacoes' => 'nullable|string|max:1000',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'forma_pagamento' => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,OUTROS',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
            'orcamento_id' => 'nullable|exists:orcamentos,id',
        ]);

        $this->service->criar([
            'centro_custo_id' => $request->centro_custo_id,
            'conta_id' => $request->conta_id,
            'descricao' => $request->descricao,
            'valor' => $request->valor,
            'data_vencimento' => $request->data_vencimento,
            'observacoes' => $request->observacoes,
            'fornecedor_id' => $request->fornecedor_id,
            'forma_pagamento' => $request->forma_pagamento,
            'conta_financeira_id' => $request->conta_financeira_id,
            'orcamento_id' => $request->orcamento_id,
            'status' => 'em_aberto',
            'tipo' => 'avulsa',
        ]);

        return back()->with('success', 'Conta a pagar criada com sucesso!');
    }

    /**
     * MOSTRAR CONTA (para edição via AJAX)
     */
    public function show(ContaPagar $conta)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $conta->load(['centroCusto', 'conta.subcategoria.categoria', 'fornecedor', 'orcamento']);

        return response()->json($conta);
    }

    /**
     * ATUALIZAR CONTA AVULSA
     */
    public function update(Request $request, ContaPagar $conta)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id' => 'required|exists:centros_custo,id',
            'conta_id' => 'required|exists:contas,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01|max:999999.99',
            'data_vencimento' => 'required|date',
            'observacoes' => 'nullable|string|max:1000',
            'forma_pagamento' => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,OUTROS',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        $this->service->atualizar($conta->id, [
            'centro_custo_id' => $request->centro_custo_id,
            'conta_id' => $request->conta_id,
            'descricao' => $request->descricao,
            'valor' => $request->valor,
            'data_vencimento' => $request->data_vencimento,
            'observacoes' => $request->observacoes,
            'forma_pagamento' => $request->forma_pagamento,
            'conta_financeira_id' => $request->conta_financeira_id,
        ]);

        return back()->with('success', 'Conta atualizada com sucesso!');
    }

    /**
     * EXCLUIR CONTA
     */
    public function destroy(Request $request, ContaPagar $conta)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        if ($conta->status === 'pago') {
            return back()->withErrors(['error' => 'Não é possível excluir uma conta já paga.']);
        }

        if ($conta->tipo === 'fixa' && $request->has('delete_future')) {
            if ($request->delete_future === 'all') {
                ContaPagar::where('conta_fixa_pagar_id', $conta->conta_fixa_pagar_id)
                    ->where('status', '!=', 'pago')
                    ->where('data_vencimento', '>=', $conta->data_vencimento)
                    ->delete();

                return back()->with('success', 'Conta e próximas parcelas excluídas com sucesso!');
            }
        }

        $this->service->excluir($conta->id);

        return back()->with('success', 'Conta a pagar excluída com sucesso!');
    }

    /**
     * CRIAR CONTA FIXA
     */
    public function storeContaFixa(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id' => 'required|exists:centros_custo,id',
            'conta_id' => 'required|exists:contas,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01|max:999999.99',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'periodicidade' => 'required|in:SEMANAL,QUINZENAL,MENSAL,TRIMESTRAL,SEMESTRAL,ANUAL',
            'forma_pagamento' => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,DEBITO_AUTOMATICO',
            'data_inicial' => 'required|date',
            'data_fim' => 'nullable|date|after:data_inicial',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        DB::beginTransaction();
        try {
            $contaFixa = ContaFixaPagar::create([
                'centro_custo_id' => $request->centro_custo_id,
                'conta_id' => $request->conta_id,
                'descricao' => $request->descricao,
                'valor' => $request->valor,
                'dia_vencimento' => Carbon::parse($request->data_inicial)->day,
                'fornecedor_id' => $request->fornecedor_id,
                'periodicidade' => $request->periodicidade,
                'forma_pagamento' => $request->forma_pagamento,
                'data_inicial' => $request->data_inicial,
                'data_fim' => $request->data_fim,
                'conta_financeira_id' => $request->conta_financeira_id,
                'ativo' => true,
            ]);

            // Gerar contas a pagar com base na periodicidade
            $dataVencimento = Carbon::parse($request->data_inicial);
            $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;
            $parcelasGeradas = 0;

            // Armazenar o dia original para tratamento de meses com dias diferentes
            $diaOriginal = $dataVencimento->day;

            // Para periodicidade MENSAL, gerar 12 parcelas
            // Para outras, gerar proporcionalmente
            $maxParcelas = match ($request->periodicidade) {
                'SEMANAL' => 52, // 1 ano de semanas
                'QUINZENAL' => 24, // 1 ano quinzenal
                'MENSAL' => 12, // 1 ano
                'TRIMESTRAL' => 4, // 1 ano
                'SEMESTRAL' => 2, // 1 ano
                'ANUAL' => 1, // 1 parcela
                default => 12
            };

            for ($i = 0; $i < $maxParcelas; $i++) {
                // Parar se ultrapassar data_fim
                if ($dataFim && $dataVencimento->gt($dataFim)) {
                    break;
                }

                // Para SEMANAL, usar a data diretamente, sem ajuste de dia do mês
                if ($contaFixa->periodicidade === 'SEMANAL') {
                    $dataVencimentoAjustada = $dataVencimento->copy();
                } else {
                    // AJUSTE: Para datas 29/30/31, usar o último dia do mês se necessário
                    $diaDesejado = $contaFixa->dia_vencimento;
                    $ultimoDiaDoMes = $dataVencimento->copy()->endOfMonth()->day;
                    $diaVencimento = min($diaDesejado, $ultimoDiaDoMes);
                    $dataVencimentoAjustada = $dataVencimento->copy()->day($diaVencimento);
                }

                ContaPagar::create([
                    'centro_custo_id' => $contaFixa->centro_custo_id,
                    'conta_id' => $contaFixa->conta_id,
                    'conta_fixa_pagar_id' => $contaFixa->id,
                    'fornecedor_id' => $contaFixa->fornecedor_id,
                    'descricao' => $contaFixa->descricao.' - '.$dataVencimentoAjustada->format('d/m/Y'),
                    'valor' => $contaFixa->valor,
                    'data_vencimento' => $dataVencimentoAjustada->format('Y-m-d'),
                    'forma_pagamento' => $contaFixa->forma_pagamento,
                    'conta_financeira_id' => $contaFixa->conta_financeira_id,
                    'status' => 'em_aberto',
                    'tipo' => 'fixa',
                    'user_id' => auth()->id(),
                ]);

                $parcelasGeradas++;

                // Avançar conforme periodicidade
                switch ($contaFixa->periodicidade) {
                    case 'SEMANAL':
                        $dataVencimento->addWeek();
                        break;
                    case 'QUINZENAL':
                        $dataVencimento->addWeeks(2);
                        break;
                    case 'MENSAL':
                        $dataVencimento->addMonthNoOverflow();
                        // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                    case 'TRIMESTRAL':
                        $dataVencimento->addMonthsNoOverflow(3);
                        // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                    case 'SEMESTRAL':
                        $dataVencimento->addMonthsNoOverflow(6);
                        // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                    case 'ANUAL':
                        $dataVencimento->addYearNoOverflow();
                        // Se o dia original era 30 ou 31 e o mês não tem esse dia, ajustar para o último dia
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                }
            }

            DB::commit();

            return back()->with('success', "Despesa fixa criada! {$parcelasGeradas} parcelas geradas com sucesso!");
        } catch (\Exception $e) {
            DB::rollback();

            return back()->withErrors(['error' => 'Erro ao criar despesa fixa: '.$e->getMessage()]);
        }
    }

    /**
     * LISTAR CONTAS FIXAS
     */
    public function contasFixas()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $contasFixas = ContaFixaPagar::with([
            'centroCusto:id,nome',
            'conta.subcategoria.categoria:id,nome',
        ])->orderBy('dia_vencimento')->get();

        return view('financeiro.contas-fixas-pagar', compact('contasFixas'));
    }

    /**
     * MOSTRAR CONTA FIXA (para edição via AJAX)
     */
    public function showContaFixa(ContaFixaPagar $contaFixa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $contaFixa->load(['centroCusto', 'conta.subcategoria.categoria', 'fornecedor']);

        return response()->json($contaFixa);
    }

    /**
     * ATUALIZAR CONTA FIXA
     */
    public function updateContaFixa(Request $request, ContaFixaPagar $contaFixa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id' => 'required|exists:centros_custo,id',
            'conta_id' => 'required|exists:contas,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01|max:999999.99',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'periodicidade' => 'required|in:SEMANAL,QUINZENAL,MENSAL,TRIMESTRAL,SEMESTRAL,ANUAL',
            'forma_pagamento' => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,DEBITO_AUTOMATICO',
            'data_inicial' => 'required|date',
            'data_fim' => 'nullable|date|after:data_inicial',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        DB::transaction(function () use ($request, $contaFixa) {
            // Atualizar a conta fixa
            $contaFixa->update([
                'centro_custo_id' => $request->centro_custo_id,
                'conta_id' => $request->conta_id,
                'descricao' => $request->descricao,
                'valor' => $request->valor,
                'dia_vencimento' => Carbon::parse($request->data_inicial)->day,
                'fornecedor_id' => $request->fornecedor_id,
                'periodicidade' => $request->periodicidade,
                'forma_pagamento' => $request->forma_pagamento,
                'data_inicial' => $request->data_inicial,
                'data_fim' => $request->data_fim,
                'conta_financeira_id' => $request->conta_financeira_id,
            ]);

            // Atualizar todas as parcelas em aberto vinculadas a essa conta fixa, incluindo as datas
            $parcelas = ContaPagar::where('conta_fixa_pagar_id', $contaFixa->id)
                ->where('status', '!=', 'pago')
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $dataVencimento = Carbon::parse($request->data_inicial);
            $diaOriginal = $dataVencimento->day;

            foreach ($parcelas as $parcela) {
                // Calcular nova data de vencimento conforme periodicidade
                $dataVencimentoAjustada = null;
                switch ($request->periodicidade) {
                    case 'SEMANAL':
                        $dataVencimentoAjustada = $dataVencimento->copy();
                        $dataVencimento->addWeek();
                        break;
                    case 'QUINZENAL':
                        $dataVencimentoAjustada = $dataVencimento->copy();
                        $dataVencimento->addWeeks(2);
                        break;
                    case 'MENSAL':
                        $ultimoDiaDoMes = $dataVencimento->copy()->endOfMonth()->day;
                        $diaVencimento = min($diaOriginal, $ultimoDiaDoMes);
                        $dataVencimentoAjustada = $dataVencimento->copy()->day($diaVencimento);
                        $dataVencimento->addMonthNoOverflow();
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                    case 'TRIMESTRAL':
                        $ultimoDiaDoMes = $dataVencimento->copy()->endOfMonth()->day;
                        $diaVencimento = min($diaOriginal, $ultimoDiaDoMes);
                        $dataVencimentoAjustada = $dataVencimento->copy()->day($diaVencimento);
                        $dataVencimento->addMonthsNoOverflow(3);
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                    case 'SEMESTRAL':
                        $ultimoDiaDoMes = $dataVencimento->copy()->endOfMonth()->day;
                        $diaVencimento = min($diaOriginal, $ultimoDiaDoMes);
                        $dataVencimentoAjustada = $dataVencimento->copy()->day($diaVencimento);
                        $dataVencimento->addMonthsNoOverflow(6);
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                    case 'ANUAL':
                        $ultimoDiaDoMes = $dataVencimento->copy()->endOfMonth()->day;
                        $diaVencimento = min($diaOriginal, $ultimoDiaDoMes);
                        $dataVencimentoAjustada = $dataVencimento->copy()->day($diaVencimento);
                        $dataVencimento->addYearNoOverflow();
                        if ($diaOriginal >= 30 && $dataVencimento->day < $diaOriginal) {
                            $dataVencimento->endOfMonth();
                        }
                        break;
                }
                $parcela->update([
                    'centro_custo_id' => $request->centro_custo_id,
                    'conta_id' => $request->conta_id,
                    'fornecedor_id' => $request->fornecedor_id,
                    'valor' => $request->valor,
                    'forma_pagamento' => $request->forma_pagamento,
                    'conta_financeira_id' => $request->conta_financeira_id,
                    'data_vencimento' => $dataVencimentoAjustada ? $dataVencimentoAjustada->format('Y-m-d') : $parcela->data_vencimento,
                ]);
            }
        });

        $parcelasAtualizadas = ContaPagar::where('conta_fixa_pagar_id', $contaFixa->id)
            ->where('status', '!=', 'pago')
            ->count();

        return back()->with('success', "Despesa fixa atualizada com sucesso! {$parcelasAtualizadas} parcela(s) em aberto foram atualizadas.");
    }

    /**
     * DESATIVAR CONTA FIXA
     */
    public function desativarContaFixa(ContaFixaPagar $contaFixa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $contaFixa->update(['ativo' => false]);

        return back()->with('success', 'Conta fixa desativada com sucesso!');
    }

    /**
     * ATIVAR CONTA FIXA
     */
    public function ativarContaFixa(ContaFixaPagar $contaFixa)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $contaFixa->update(['ativo' => true]);

        return back()->with('success', 'Conta fixa ativada com sucesso!');
    }

    /**
     * API: BUSCAR SUBCATEGORIAS POR CATEGORIA
     */
    public function getSubcategorias($categoriaId)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoriaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($subcategorias);
    }

    /**
     * API: BUSCAR CONTAS POR SUBCATEGORIA
     */
    public function getContas($subcategoriaId)
    {
        $contas = Conta::where('subcategoria_id', $subcategoriaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($contas);
    }

    // ================= ANEXOS =================

    /**
     * Listar anexos de uma conta a pagar
     */
    public function getAnexos(ContaPagar $conta)
    {
        $anexos = $conta->anexos()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'anexos' => $anexos,
        ]);
    }

    /**
     * Fazer upload de anexos (NF ou Boleto)
     */
    public function storeAnexo(Request $request, ContaPagar $conta)
    {
        $request->validate([
            'tipo' => 'required|in:nf,boleto',
            'arquivos' => 'required|array',
            'arquivos.*' => 'required|file|mimes:pdf|max:10240', // Máx 10MB por arquivo
        ]);

        try {
            DB::beginTransaction();

            $uploadedCount = 0;
            $errors = [];

            foreach ($request->file('arquivos') as $arquivo) {
                try {
                    // Gerar nome único para o arquivo
                    $nomeOriginal = $arquivo->getClientOriginalName();
                    $extensao = $arquivo->getClientOriginalExtension();
                    $nomeArquivo = uniqid().'_'.time().'.'.$extensao;

                    // Salvar arquivo no storage
                    $caminho = $arquivo->storeAs('anexos/contas_pagar', $nomeArquivo, 'public');

                    // Criar registro no banco
                    ContaPagarAnexo::create([
                        'conta_pagar_id' => $conta->id,
                        'tipo' => $request->tipo,
                        'nome_original' => $nomeOriginal,
                        'nome_arquivo' => $nomeArquivo,
                        'caminho' => $caminho,
                        'tamanho' => $arquivo->getSize(),
                    ]);

                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Erro ao fazer upload de {$arquivo->getClientOriginalName()}: {$e->getMessage()}";
                }
            }

            DB::commit();

            if ($uploadedCount > 0) {
                $tipoFormatado = $request->tipo === 'nf' ? 'Nota Fiscal' : 'Boleto';
                $mensagem = $uploadedCount === 1
                    ? "Anexo ({$tipoFormatado}) enviado com sucesso!"
                    : "{$uploadedCount} anexos ({$tipoFormatado}) enviados com sucesso!";

                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'uploaded' => $uploadedCount,
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum arquivo foi enviado',
                    'errors' => $errors,
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar anexos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Excluir um anexo
     */
    public function destroyAnexo(ContaPagarAnexo $anexo)
    {
        try {
            // Excluir arquivo do storage
            if (Storage::disk('public')->exists($anexo->caminho)) {
                Storage::disk('public')->delete($anexo->caminho);
            }

            // Excluir registro do banco
            $anexo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Anexo excluído com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir anexo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download de um anexo
     */
    public function downloadAnexo(ContaPagarAnexo $anexo)
    {
        try {
            \Log::info('Download anexo iniciado', [
                'anexo_id' => $anexo->id,
                'caminho' => $anexo->caminho,
                'nome_original' => $anexo->nome_original,
            ]);

            // Usar o disco 'public' configurado para data/uploads
            if (! Storage::disk('public')->exists($anexo->caminho)) {
                \Log::error('Arquivo não encontrado no storage', [
                    'caminho' => $anexo->caminho,
                    'anexo_id' => $anexo->id,
                ]);
                abort(404, 'Arquivo não encontrado no servidor');
            }

            \Log::info('Arquivo encontrado, iniciando download', [
                'caminho' => $anexo->caminho,
            ]);

            return Storage::disk('public')->download($anexo->caminho, $anexo->nome_original);
        } catch (\Exception $e) {
            \Log::error('Erro ao fazer download do anexo', [
                'anexo_id' => $anexo->id ?? null,
                'erro' => $e->getMessage(),
            ]);
            abort(500, 'Erro ao fazer download do anexo: '.$e->getMessage());
        }
    }
}
