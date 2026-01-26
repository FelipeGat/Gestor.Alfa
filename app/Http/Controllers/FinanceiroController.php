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
        $orcamentosFinanceiro = Orcamento::with(['cliente', 'preCliente', 'empresa'])
            ->whereIn('status', ['financeiro', 'aguardando_pagamento'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | KPIs (BASEADOS NAS COBRANÇAS)
        |--------------------------------------------------------------------------
        */
        $hoje = Carbon::today();

        $totalReceber = Cobranca::where('status', 'pendente')->sum('valor');
        $totalPago    = Cobranca::where('status', 'pago')->sum('valor');

        $totalVencido = Cobranca::where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->sum('valor');

        $venceHoje = Cobranca::where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->sum('valor');

        $qtdVencidos = Cobranca::where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->count();

        $qtdVenceHoje = Cobranca::where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->count();

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('financeiro.index', compact(
            'orcamentosFinanceiro',
            'totalReceber',
            'totalPago',
            'totalVencido',
            'venceHoje',
            'qtdVencidos',
            'qtdVenceHoje',
            'empresas'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | GERAR COBRANÇA (ORÇAMENTO → FINANCEIRO REAL)
    |--------------------------------------------------------------------------
    */
    public function gerarCobranca(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $orcamento->refresh();

        abort_if(
            $orcamento->status !== 'financeiro',
            422,
            'Este orçamento não está disponível para cobrança.'
        );

        abort_if(
            $orcamento->cobranca,
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
                'cliente_id'      => $orcamento->cliente_id, // pode ser null se for pré-cliente
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
}
