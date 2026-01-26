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
use Illuminate\Validation\ValidationException;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Segurança (admin ou perfil financeiro)
        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */
        $empresaId    = $request->get('empresa_id');
        $statusFiltro = $request->get('status');

        /*
        |--------------------------------------------------------------------------
        | ================== BLOCO 1 — ORÇAMENTOS (PIPELINE FINANCEIRO)
        |--------------------------------------------------------------------------
        */
        $statusFinanceiros = [
            'financeiro',
            'aguardando_pagamento',
        ];

        $orcamentoQuery = Orcamento::query()
            ->whereIn('status', $statusFinanceiros);

        if ($empresaId) {
            $orcamentoQuery->where('empresa_id', $empresaId);
        }

        if ($statusFiltro) {
            $orcamentoQuery->where('status', $statusFiltro);
        }

        // KPIs
        $qtdFinanceiro = (clone $orcamentoQuery)->where('status', 'financeiro')->count();
        $qtdAguardandoPagamento = (clone $orcamentoQuery)->where('status', 'aguardando_pagamento')->count();

        $valorTotalAberto = (clone $orcamentoQuery)->sum('valor_total');

        $metricasFiltradas = (clone $orcamentoQuery)
            ->selectRaw('COUNT(*) as qtd, SUM(valor_total) as valor_total')
            ->first();

        // Gráfico por status
        $orcamentosPorStatus = (clone $orcamentoQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Gráfico por empresa
        $orcamentosPorEmpresa = (clone $orcamentoQuery)
            ->select(
                'empresa_id',
                DB::raw('COUNT(*) as total_qtd'),
                DB::raw('SUM(valor_total) as total_valor')
            )
            ->groupBy('empresa_id')
            ->with('empresa')
            ->get();

        // Lista para ação do financeiro
        $orcamentosFinanceiro = (clone $orcamentoQuery)
            ->with(['cliente', 'preCliente'])
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ================== BLOCO 2 — COBRANÇAS (FINANCEIRO REAL)
        |--------------------------------------------------------------------------
        */
        $hoje = Carbon::today();

        $cobrancaQuery = Cobranca::with('cliente');

        $totalReceber = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->sum('valor');

        $totalPago = (clone $cobrancaQuery)
            ->where('status', 'pago')
            ->sum('valor');

        $totalVencido = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->sum('valor');

        $venceHoje = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->sum('valor');

        $qtdVencidos = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->count();

        $qtdVenceHoje = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->count();

        $cobrancasPendentes = (clone $cobrancaQuery)
            ->where('status', 'pendente')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | AUXILIARES
        |--------------------------------------------------------------------------
        */
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('financeiro.index', compact(
            // filtros
            'empresas',
            'empresaId',
            'statusFiltro',

            // orçamentos
            'qtdFinanceiro',
            'qtdAguardandoPagamento',
            'valorTotalAberto',
            'metricasFiltradas',
            'orcamentosPorStatus',
            'orcamentosPorEmpresa',
            'orcamentosFinanceiro',

            // cobranças
            'totalReceber',
            'totalPago',
            'totalVencido',
            'venceHoje',
            'qtdVencidos',
            'qtdVenceHoje',
            'cobrancasPendentes'
        ));
    }

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

        // Garante estado atualizado
        $orcamento->refresh();

        // Só pode gerar cobrança se estiver no financeiro
        abort_if(
            $orcamento->status !== 'financeiro',
            422,
            'Este orçamento não está mais disponível para cobrança.'
        );

        // Não permitir cobrança duplicada
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
                'cliente_id'      => $orcamento->cliente_id,
                'descricao'       => $dados['descricao']
                    ?? 'Cobrança do orçamento ' . $orcamento->numero_orcamento,
                'valor'           => $dados['valor'],
                'data_vencimento' => $dados['data_vencimento'],
                'status'          => 'pendente',
            ]);

            // Avança o fluxo
            $orcamento->update([
                'status' => 'aguardando_pagamento',
            ]);
        });

        return redirect()
            ->route('financeiro.index')
            ->with('success', 'Cobrança gerada com sucesso.');
    }


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

            // Se a cobrança veio de um orçamento,
            // devolve o orçamento para o financeiro
            if ($cobranca->orcamento && $cobranca->orcamento->status === 'aguardando_pagamento') {
                $cobranca->orcamento->update([
                    'status' => 'financeiro',
                ]);
            }

            // Exclui a cobrança
            $cobranca->delete();
        });

        return redirect()
            ->route('financeiro.contasareceber')
            ->with('success', 'Cobrança excluída e orçamento devolvido ao financeiro.');
    }


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

        /*
    |--------------------------------------------------------------------------
    | QUERY BASE
    |--------------------------------------------------------------------------
    */
        $query = Cobranca::query()
            ->with([
                'cliente.telefones',
                'cliente.emails',
                'orcamento',
            ]);

        /*
    |--------------------------------------------------------------------------
    | FILTRO DE BUSCA
    |--------------------------------------------------------------------------
    */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($qc) use ($search) {
                        $qc->where('nome', 'like', "%{$search}%");
                    });
            });
        }

        /*
    |--------------------------------------------------------------------------
    | FILTRO POR STATUS
    |--------------------------------------------------------------------------
    */
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        /*
    |--------------------------------------------------------------------------
    | FILTRO POR PERÍODO (VENCIMENTO)
    |--------------------------------------------------------------------------
    */
        if ($request->filled('periodo')) {
            switch ($request->periodo) {
                case 'dia':
                    $query->whereDate('data_vencimento', $hoje);
                    break;

                case 'semana':
                    $query->whereBetween('data_vencimento', [
                        $hoje->startOfWeek(),
                        $hoje->endOfWeek(),
                    ]);
                    break;

                case 'mes':
                    $query->whereMonth('data_vencimento', $hoje->month)
                        ->whereYear('data_vencimento', $hoje->year);
                    break;
            }
        }

        /*
    |--------------------------------------------------------------------------
    | PAGINAÇÃO
    |--------------------------------------------------------------------------
    */
        $cobrancas = $query
            ->orderBy('data_vencimento')
            ->paginate(10)
            ->withQueryString();

        return view('financeiro.contasareceber', compact(
            'cobrancas',
            'hoje'
        ));
    }
}
