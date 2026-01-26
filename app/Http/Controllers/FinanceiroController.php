<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Empresa;
use App\Models\Cobranca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        /*
        |--------------------------------------------------------------------------
        | ORÇAMENTOS NO PIPELINE FINANCEIRO
        |--------------------------------------------------------------------------
        */
        $orcamentoQuery = Orcamento::query()
            ->where('status', 'financeiro')
            ->with(['cliente', 'preCliente', 'empresa']);

        // 🔍 Busca
        if ($request->filled('search')) {
            $search = $request->search;

            $orcamentoQuery->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', "%{$search}%")
                    ->orWhereHas(
                        'cliente',
                        fn($c) =>
                        $c->where('nome', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'preCliente',
                        fn($p) =>
                        $p->where('nome_fantasia', 'like', "%{$search}%")
                    );
            });
        }

        // 🏢 Empresa (múltiplo)
        if ($request->filled('empresa_id')) {
            $orcamentoQuery->whereIn('empresa_id', (array) $request->empresa_id);
        }

        $orcamentosFinanceiro = $orcamentoQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | KPIs FINANCEIROS (COBRANÇAS)
        |--------------------------------------------------------------------------
        */
        $hoje = Carbon::today();

        $cobrancaBase = Cobranca::query();

        $totalReceber = (clone $cobrancaBase)
            ->where('status', 'pendente')
            ->sum('valor');

        $totalPago = (clone $cobrancaBase)
            ->where('status', 'pago')
            ->sum('valor');

        $totalVencido = (clone $cobrancaBase)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->sum('valor');

        $venceHoje = (clone $cobrancaBase)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->sum('valor');

        $qtdVencidos = (clone $cobrancaBase)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->count();

        $qtdVenceHoje = (clone $cobrancaBase)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->count();

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('financeiro.index', compact(
            'orcamentosFinanceiro',
            'empresas',
            'totalReceber',
            'totalPago',
            'totalVencido',
            'venceHoje',
            'qtdVencidos',
            'qtdVenceHoje'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | GERAR COBRANÇA
    |--------------------------------------------------------------------------
    */
    public function gerarCobranca(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $orcamento->refresh();

        // Só pode gerar cobrança se estiver no financeiro
        abort_if(
            $orcamento->status !== 'financeiro',
            422,
            'Este orçamento não está mais disponível para cobrança.'
        );

        // BLOQUEIA PRÉ-CLIENTE
        abort_if(
            is_null($orcamento->cliente_id),
            422,
            'Este orçamento é de um pré-cliente. Converta-o em cliente antes de gerar a cobrança.'
        );

        // Evita cobrança duplicada
        abort_if(
            $orcamento->cobranca()->exists(),
            422,
            'Este orçamento já possui cobrança.'
        );

        $dados = $request->validate([
            'valor'           => ['required', 'numeric', 'min:0.01'],
            'data_vencimento' => ['required', 'date'],
            'descricao'       => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($orcamento, $dados) {

            Cobranca::create([
                'orcamento_id'    => $orcamento->id,
                'cliente_id'      => $orcamento->cliente_id,
                'descricao'       => $dados['descricao']
                    ?? 'Cobrança do orçamento ' . $orcamento->numero_orcamento,
                'valor'           => $dados['valor'],
                'data_vencimento' => $dados['data_vencimento'],
                'status'          => 'pendente',
            ]);

            $orcamento->update([
                'status' => 'aguardando_pagamento',
            ]);
        });

        return redirect()
            ->route('financeiro.index')
            ->with('success', 'Cobrança gerada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | CONTAS A RECEBER
    |--------------------------------------------------------------------------
    */
    public function contasAReceber(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $hoje = Carbon::today();

        $query = Cobranca::query()
            ->with([
                'cliente.telefones',
                'cliente.emails',
                'orcamento',
            ]);

        // 🔍 Busca
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                    ->orWhereHas(
                        'cliente',
                        fn($c) =>
                        $c->where('nome', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'orcamento',
                        fn($o) =>
                        $o->where('numero_orcamento', 'like', "%{$search}%")
                    );
            });
        }

        // 📌 Status
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        // 📅 Período
        if ($request->filled('periodo')) {
            match ($request->periodo) {
                'dia'    => $query->whereDate('data_vencimento', $hoje),
                'semana' => $query->whereBetween('data_vencimento', [$hoje->startOfWeek(), $hoje->endOfWeek()]),
                'mes'    => $query->whereMonth('data_vencimento', $hoje->month)
                    ->whereYear('data_vencimento', $hoje->year),
                default  => null
            };
        }

        $cobrancas = $query
            ->orderBy('data_vencimento')
            ->paginate(10)
            ->withQueryString();

        return view('financeiro.contasareceber', compact(
            'cobrancas',
            'hoje'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR COBRANÇA
    |--------------------------------------------------------------------------
    */
    public function destroyCobranca(Cobranca $cobranca)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        DB::transaction(function () use ($cobranca) {
            if ($cobranca->orcamento && $cobranca->orcamento->status === 'aguardando_pagamento') {
                $cobranca->orcamento->update([
                    'status' => 'financeiro',
                ]);
            }

            $cobranca->delete();
        });

        return redirect()
            ->route('financeiro.contasareceber')
            ->with('success', 'Cobrança excluída e orçamento devolvido ao financeiro.');
    }
}
