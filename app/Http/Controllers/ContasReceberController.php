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

        // Filtro de Período por Vencimento
        if ($request->filled('vencimento_inicio')) {
            $query->where('data_vencimento', '>=', $request->input('vencimento_inicio'));
        }
        if ($request->filled('vencimento_fim')) {
            $query->where('data_vencimento', '<=', $request->input('vencimento_fim'));
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
            'contadoresStatus'
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

        DB::transaction(function () use ($cobranca, $tipoExclusao) {
            $orcamento = $cobranca->orcamento;

            if ($tipoExclusao === 'todas' && $cobranca->orcamento_id) {
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
            $cobranca->update([
                'status' => 'pendente',
                'pago_em' => null,
                'conta_financeira_id' => null,
                // Mantém a forma_pagamento para histórico
            ]);
        });

        return back()->with('success', 'Cobrança estornada e devolvida para Contas a Receber.');
    }
}
