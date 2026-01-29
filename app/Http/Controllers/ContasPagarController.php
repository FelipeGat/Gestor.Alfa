<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaFixaPagar;
use App\Models\CentroCusto;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Conta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        ])->select('contas_pagar.*');

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

        $request->validate([
            'forma_pagamento'     => 'required|string|max:50',
            'conta_financeira_id' => 'nullable|exists:contas_financeiras,id',
        ]);

        // Atualizar a conta
        $conta->update([
            'status'              => 'pago',
            'pago_em'             => now(),
            'forma_pagamento'     => $request->forma_pagamento,
            'conta_financeira_id' => $request->conta_financeira_id,
        ]);

        // Atualizar saldo da conta bancária se informada
        if ($request->conta_financeira_id) {
            $contaBancaria = \App\Models\ContaFinanceira::find($request->conta_financeira_id);
            if ($contaBancaria) {
                $contaBancaria->saldo -= $conta->valor;
                $contaBancaria->save();
            }
        }

        return back()->with('success', 'Conta marcada como paga e saldo bancário atualizado!');
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

                ContaPagar::create([
                    'centro_custo_id'      => $contaFixa->centro_custo_id,
                    'conta_id'             => $contaFixa->conta_id,
                    'conta_fixa_pagar_id'  => $contaFixa->id,
                    'fornecedor_id'        => $contaFixa->fornecedor_id,
                    'descricao'            => $contaFixa->descricao . ' - ' . $dataVencimento->format('m/Y'),
                    'valor'                => $contaFixa->valor,
                    'data_vencimento'      => $dataVencimento->format('Y-m-d'),
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
                        $dataVencimento->addMonth();
                        break;
                    case 'TRIMESTRAL':
                        $dataVencimento->addMonths(3);
                        break;
                    case 'SEMESTRAL':
                        $dataVencimento->addMonths(6);
                        break;
                    case 'ANUAL':
                        $dataVencimento->addYear();
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
}
