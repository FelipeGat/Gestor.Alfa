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
    /**
     * Exibe o painel principal do financeiro com orçamentos pendentes.
     */
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

        // ================= KPIs GERAIS (Visão Global do Financeiro) =================
        $hoje = Carbon::today();
        $kpisGerais = [
            'total_receber' => Cobranca::where('status', '!=', 'pago')->sum('valor'),
            'total_pago'    => Cobranca::where('status', 'pago')->sum('valor'),
            'total_vencido' => Cobranca::where('status', '!=', 'pago')->whereDate('data_vencimento', '<', $hoje)->sum('valor'),
            'vence_hoje'    => Cobranca::where('status', '!=', 'pago')->whereDate('data_vencimento', $hoje)->sum('valor'),
        ];

        // ================= QUERY BASE PARA ORÇAMENTOS =================
        $query = Orcamento::with(['cliente', 'preCliente', 'empresa'])
            ->whereIn('status', ['financeiro', 'aguardando_pagamento']);

        // ================= APLICAÇÃO DOS FILTROS =================

        // Filtro de Busca (Número do Orçamento, Cliente ou Pré-Cliente)
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', $search)
                    ->orWhereHas('cliente', fn($sq) => $sq->where('nome_fantasia', 'like', $search))
                    ->orWhereHas('preCliente', fn($sq) => $sq->where('nome_fantasia', 'like', $search));
            });
        }

        // Filtro de Empresa
        if ($request->filled('empresa_id') && is_array($request->input('empresa_id'))) {
            $query->whereIn('empresa_id', $request->input('empresa_id'));
        }

        // Clonar query para contadores antes do filtro de status
        $queryParaContadores = clone $query;

        // Filtro de Status
        if ($request->filled('status') && is_array($request->input('status'))) {
            $query->whereIn('status', $request->input('status'));
        }

        // ================= CÁLCULO DE TOTAIS E CONTADORES =================
        $totalGeralFiltrado = (clone $query)->sum('valor_total');

        $contadoresStatus = [
            'financeiro' => (clone $queryParaContadores)->where('status', 'financeiro')->count(),
            'aguardando_pagamento' => (clone $queryParaContadores)->where('status', 'aguardando_pagamento')->count(),
        ];

        // ================= PAGINAÇÃO =================
        $orcamentos = $query->orderBy('updated_at', 'desc')->paginate(10)->withQueryString();
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();

        // Enviando todas as variáveis necessárias para a nova view
        return view('financeiro.index', compact(
            'kpisGerais',
            'orcamentos',
            'empresas',
            'contadoresStatus',
            'totalGeralFiltrado'
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
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
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

        if (!$orcamento->cliente_id && $orcamento->pre_cliente_id) {
            return redirect()
                ->back()
                ->with('error', 'Não é possível gerar cobrança para Pré-Clientes. Favor cadastrar como cliente.');
        }

        $dados = $request->validate([
            'valor'           => ['required', 'numeric', 'min:0.01'],
            'data_vencimento' => ['required', 'date'],
            'descricao'       => ['nullable', 'string', 'max:255'],
        ]);


        DB::transaction(function () use ($orcamento, $dados) {

            Cobranca::create([
                'orcamento_id'    => $orcamento->id,
                'cliente_id'      => $orcamento->cliente_id,
                'descricao'       => $dados['descricao'] ?? 'Cobrança do orçamento ' . $orcamento->numero_orcamento,
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
