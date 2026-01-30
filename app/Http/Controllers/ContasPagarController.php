<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaPagarAnexo;
use App\Models\ContaFixaPagar;
use App\Models\CentroCusto;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Conta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContasPagarController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // ================= QUERY BASE =================
        $query = ContaPagar::with([
            'centroCusto:id,nome,tipo',
            'conta.subcategoria.categoria:id,nome,tipo'
        ])
            ->select('contas_pagar.*')
            ->where('status', '!=', 'pago'); // Excluir contas pagas (vão para Movimentação)

        // ================= FILTROS =================

        // Filtro de Busca
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('descricao', 'like', $searchTerm)
                    ->orWhereHas('centroCusto', fn($sq) => $sq->where('nome', 'like', $searchTerm));
            });
        }

        // Filtro de Período
        $vencimentoInicio = $request->input('vencimento_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $vencimentoFim = $request->input('vencimento_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        if ($vencimentoInicio) {
            $query->where('data_vencimento', '>=', $vencimentoInicio);
        }
        if ($vencimentoFim) {
            $query->where('data_vencimento', '<=', $vencimentoFim);
        }

        // Filtro por Centro de Custo
        if ($request->filled('centro_custo_id')) {
            $query->where('centro_custo_id', $request->input('centro_custo_id'));
        }

        // Filtro por Tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        // Clone para contadores
        $queryParaContadores = clone $query;

        // Filtro de Status
        if ($request->filled('status') && is_array($request->input('status')) && !empty($request->input('status')[0])) {
            $query->where(function ($q) use ($request) {
                foreach ($request->input('status') as $status) {
                    if ($status === 'em_aberto') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', 'em_aberto')->whereDate('data_vencimento', '>=', today());
                        });
                    } elseif ($status === 'vencido') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today());
                        });
                    } elseif ($status === 'pago') {
                        $q->orWhere('status', 'pago');
                    }
                }
            });
        }

        // ================= KPIS =================
        $kpisQueryBase = ContaPagar::query();

        if ($vencimentoInicio) {
            $kpisQueryBase->where('data_vencimento', '>=', $vencimentoInicio);
        }
        if ($vencimentoFim) {
            $kpisQueryBase->where('data_vencimento', '<=', $vencimentoFim);
        }

        $kpis = [
            'a_pagar'    => (clone $kpisQueryBase)->where('status', 'em_aberto')->whereDate('data_vencimento', '>=', today())->sum('valor'),
            'pago'       => (clone $kpisQueryBase)->where('status', 'pago')->sum('valor'),
            'vencido'    => (clone $kpisQueryBase)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->sum('valor'),
            'vence_hoje' => (clone $kpisQueryBase)->where('status', 'em_aberto')->whereDate('data_vencimento', today())->sum('valor'),
        ];

        $totalGeralFiltrado = (clone $kpisQueryBase)->sum('valor');

        // ================= CONTADORES =================
        $contadoresStatus = [
            'em_aberto' => (clone $queryParaContadores)->where('status', 'em_aberto')->whereDate('data_vencimento', '>=', today())->count(),
            'vencido'   => (clone $queryParaContadores)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->count(),
            'pago'      => (clone $queryParaContadores)->where('status', 'pago')->count(),
        ];

        $contadoresTipo = [
            'avulsa' => (clone $queryParaContadores)->where('tipo', 'avulsa')->count(),
            'fixa'   => (clone $queryParaContadores)->where('tipo', 'fixa')->count(),
        ];

        // ================= PAGINAÇÃO =================
        $contas = $query
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Dados para filtros
        $centrosCusto = CentroCusto::where('ativo', true)->orderBy('nome')->get();

        return view('financeiro.contasapagar', compact(
            'contas',
            'kpis',
            'totalGeralFiltrado',
            'contadoresStatus',
            'contadoresTipo',
            'vencimentoInicio',
            'vencimentoFim',
            'centrosCusto'
        ));
    }

    /**
     * MARCAR COMO PAGO
     */
    public function marcarComoPago(Request $request, ContaPagar $conta)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
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
            'forma_pagamento'     => 'required|string|max:50',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
            'valor_pago'          => 'required|numeric|min:0.01',
            'criar_nova_conta'    => 'nullable|boolean',
            'valor_restante'      => 'nullable|numeric|min:0',
            'data_vencimento_original' => 'nullable|date',
        ]);

        $valorPago = floatval($request->valor_pago);
        $valorTotal = floatval($conta->valor);

        // Validações de valor
        if ($valorPago <= 0) {
            return back()->with('error', 'O valor pago deve ser maior que zero.');
        }

        if ($valorPago > $valorTotal) {
            return back()->with('error', 'O valor pago não pode ser maior que o valor total da conta.');
        }

        DB::transaction(function () use ($conta, $request, $valorPago, $valorTotal) {
            // Atualizar a conta atual com o valor pago
            $conta->update([
                'status'              => 'pago',
                'pago_em'             => now(),
                'valor'               => $valorPago, // Atualiza para o valor efetivamente pago
                'forma_pagamento'     => $request->forma_pagamento,
                'conta_financeira_id' => $request->conta_financeira_id,
            ]);

            // Atualizar saldo da conta bancária se informada (DESPESA = DIMINUI O SALDO)
            if ($request->conta_financeira_id) {
                $contaBancaria = \App\Models\ContaFinanceira::find($request->conta_financeira_id);
                if ($contaBancaria) {
                    $contaBancaria->decrement('saldo', $valorPago);
                }
            }

            // Se houver valor restante, criar nova conta
            if ($request->criar_nova_conta && $request->valor_restante > 0) {
                $valorRestante = floatval($request->valor_restante);

                // Criar nova conta com o valor restante
                ContaPagar::create([
                    'centro_custo_id'     => $conta->centro_custo_id,
                    'conta_id'            => $conta->conta_id,
                    'fornecedor_id'       => $conta->fornecedor_id,
                    'conta_fixa_pagar_id' => $conta->conta_fixa_pagar_id,
                    'descricao'           => $conta->descricao . ' (Restante)',
                    'valor'               => $valorRestante,
                    'data_vencimento'     => $request->data_vencimento_original ?? $conta->data_vencimento,
                    'status'              => 'em_aberto',
                    'tipo'                => $conta->tipo,
                ]);
            }
        });

        $mensagem = 'Conta marcada como paga e registrada na movimentação.';
        if ($request->criar_nova_conta && $request->valor_restante > 0) {
            $mensagem .= ' Uma nova conta foi criada com o valor restante de R$ ' . number_format($request->valor_restante, 2, ',', '.');
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id'     => 'required|exists:centros_custo,id',
            'conta_id'            => 'required|exists:contas,id',
            'descricao'           => 'required|string|max:255',
            'valor'               => 'required|numeric|min:0.01|max:999999.99',
            'data_vencimento'     => 'required|date|after_or_equal:today',
            'observacoes'         => 'nullable|string|max:1000',
            'fornecedor_id'       => 'nullable|exists:fornecedores,id',
            'forma_pagamento'     => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,OUTROS',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        ContaPagar::create([
            'centro_custo_id'     => $request->centro_custo_id,
            'conta_id'            => $request->conta_id,
            'descricao'           => $request->descricao,
            'valor'               => $request->valor,
            'data_vencimento'     => $request->data_vencimento,
            'observacoes'         => $request->observacoes,
            'fornecedor_id'       => $request->fornecedor_id,
            'forma_pagamento'     => $request->forma_pagamento,
            'conta_financeira_id' => $request->conta_financeira_id,
            'status'              => 'em_aberto',
            'tipo'                => 'avulsa',
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $conta->load(['centroCusto', 'conta.subcategoria.categoria', 'fornecedor']);

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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id'     => 'required|exists:centros_custo,id',
            'conta_id'            => 'required|exists:contas,id',
            'descricao'           => 'required|string|max:255',
            'valor'               => 'required|numeric|min:0.01|max:999999.99',
            'data_vencimento'     => 'required|date',
            'observacoes'         => 'nullable|string|max:1000',
            'forma_pagamento'     => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,OUTROS',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        $conta->update([
            'centro_custo_id'     => $request->centro_custo_id,
            'conta_id'            => $request->conta_id,
            'descricao'           => $request->descricao,
            'valor'               => $request->valor,
            'data_vencimento'     => $request->data_vencimento,
            'observacoes'         => $request->observacoes,
            'forma_pagamento'     => $request->forma_pagamento,
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        // Não permitir excluir conta paga
        if ($conta->status === 'pago') {
            return back()->withErrors(['error' => 'Não é possível excluir uma conta já paga.']);
        }

        // Se for conta fixa e tem parâmetro delete_future
        if ($conta->tipo === 'fixa' && $request->has('delete_future')) {
            if ($request->delete_future === 'all') {
                // Deletar essa e todas as próximas não pagas da mesma conta fixa
                ContaPagar::where('conta_fixa_pagar_id', $conta->conta_fixa_pagar_id)
                    ->where('status', '!=', 'pago')
                    ->where('data_vencimento', '>=', $conta->data_vencimento)
                    ->delete();

                return back()->with('success', 'Conta e próximas parcelas excluídas com sucesso!');
            }
        }

        // Deletar apenas essa conta
        $conta->delete();

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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id'     => 'required|exists:centros_custo,id',
            'conta_id'            => 'required|exists:contas,id',
            'descricao'           => 'required|string|max:255',
            'valor'               => 'required|numeric|min:0.01|max:999999.99',
            'fornecedor_id'       => 'nullable|exists:fornecedores,id',
            'periodicidade'       => 'required|in:SEMANAL,QUINZENAL,MENSAL,TRIMESTRAL,SEMESTRAL,ANUAL',
            'forma_pagamento'     => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,DEBITO_AUTOMATICO',
            'data_inicial'        => 'required|date',
            'data_fim'            => 'nullable|date|after:data_inicial',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        DB::beginTransaction();
        try {
            $contaFixa = ContaFixaPagar::create([
                'centro_custo_id'     => $request->centro_custo_id,
                'conta_id'            => $request->conta_id,
                'descricao'           => $request->descricao,
                'valor'               => $request->valor,
                'dia_vencimento'      => Carbon::parse($request->data_inicial)->day,
                'fornecedor_id'       => $request->fornecedor_id,
                'periodicidade'       => $request->periodicidade,
                'forma_pagamento'     => $request->forma_pagamento,
                'data_inicial'        => $request->data_inicial,
                'data_fim'            => $request->data_fim,
                'conta_financeira_id' => $request->conta_financeira_id,
                'ativo'               => true,
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
                'SEMANAL' => 52,      // 1 ano de semanas
                'QUINZENAL' => 24,    // 1 ano quinzenal
                'MENSAL' => 12,       // 1 ano
                'TRIMESTRAL' => 4,    // 1 ano
                'SEMESTRAL' => 2,     // 1 ano
                'ANUAL' => 1,         // 1 parcela
                default => 12
            };

            for ($i = 0; $i < $maxParcelas; $i++) {
                // Parar se ultrapassar data_fim
                if ($dataFim && $dataVencimento->gt($dataFim)) {
                    break;
                }

                // AJUSTE: Para datas 29/30/31, usar o último dia do mês se necessário
                $diaDesejado = $contaFixa->dia_vencimento;
                $ultimoDiaDoMes = $dataVencimento->copy()->endOfMonth()->day;
                $diaVencimento = min($diaDesejado, $ultimoDiaDoMes);
                $dataVencimentoAjustada = $dataVencimento->copy()->day($diaVencimento);

                ContaPagar::create([
                    'centro_custo_id'      => $contaFixa->centro_custo_id,
                    'conta_id'             => $contaFixa->conta_id,
                    'conta_fixa_pagar_id'  => $contaFixa->id,
                    'fornecedor_id'        => $contaFixa->fornecedor_id,
                    'descricao'            => $contaFixa->descricao . ' - ' . $dataVencimentoAjustada->format('m/Y'),
                    'valor'                => $contaFixa->valor,
                    'data_vencimento'      => $dataVencimentoAjustada->format('Y-m-d'),
                    'forma_pagamento'      => $contaFixa->forma_pagamento,
                    'conta_financeira_id'  => $contaFixa->conta_financeira_id,
                    'status'               => 'em_aberto',
                    'tipo'                 => 'fixa',
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
            return back()->withErrors(['error' => 'Erro ao criar despesa fixa: ' . $e->getMessage()]);
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $contasFixas = ContaFixaPagar::with([
            'centroCusto:id,nome',
            'conta.subcategoria.categoria:id,nome'
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $request->validate([
            'centro_custo_id'     => 'required|exists:centros_custo,id',
            'conta_id'            => 'required|exists:contas,id',
            'descricao'           => 'required|string|max:255',
            'valor'               => 'required|numeric|min:0.01|max:999999.99',
            'fornecedor_id'       => 'nullable|exists:fornecedores,id',
            'periodicidade'       => 'required|in:SEMANAL,QUINZENAL,MENSAL,TRIMESTRAL,SEMESTRAL,ANUAL',
            'forma_pagamento'     => 'nullable|in:PIX,BOLETO,TRANSFERENCIA,CARTAO_CREDITO,CARTAO_DEBITO,DINHEIRO,CHEQUE,DEBITO_AUTOMATICO',
            'data_inicial'        => 'required|date',
            'data_fim'            => 'nullable|date|after:data_inicial',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        DB::transaction(function () use ($request, $contaFixa) {
            // Atualizar a conta fixa
            $contaFixa->update([
                'centro_custo_id'     => $request->centro_custo_id,
                'conta_id'            => $request->conta_id,
                'descricao'           => $request->descricao,
                'valor'               => $request->valor,
                'dia_vencimento'      => Carbon::parse($request->data_inicial)->day,
                'fornecedor_id'       => $request->fornecedor_id,
                'periodicidade'       => $request->periodicidade,
                'forma_pagamento'     => $request->forma_pagamento,
                'data_inicial'        => $request->data_inicial,
                'data_fim'            => $request->data_fim,
                'conta_financeira_id' => $request->conta_financeira_id,
            ]);

            // Atualizar todas as parcelas em aberto vinculadas a essa conta fixa
            ContaPagar::where('conta_fixa_pagar_id', $contaFixa->id)
                ->where('status', '!=', 'pago')
                ->update([
                    'centro_custo_id'     => $request->centro_custo_id,
                    'conta_id'            => $request->conta_id,
                    'fornecedor_id'       => $request->fornecedor_id,
                    'valor'               => $request->valor,
                    'forma_pagamento'     => $request->forma_pagamento,
                    'conta_financeira_id' => $request->conta_financeira_id,
                ]);
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
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
            'anexos' => $anexos
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
                    $nomeArquivo = uniqid() . '_' . time() . '.' . $extensao;

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
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum arquivo foi enviado',
                    'errors' => $errors
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar anexos: ' . $e->getMessage()
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
                'message' => 'Anexo excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir anexo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download de um anexo
     */
    public function downloadAnexo(ContaPagarAnexo $anexo)
    {
        try {
            $caminhoCompleto = storage_path('app/public/' . $anexo->caminho);

            if (!file_exists($caminhoCompleto)) {
                abort(404, 'Arquivo não encontrado');
            }

            return response()->download($caminhoCompleto, $anexo->nome_original);
        } catch (\Exception $e) {
            abort(500, 'Erro ao fazer download do anexo: ' . $e->getMessage());
        }
    }
}
