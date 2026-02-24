<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\ContaFinanceira;
use App\Models\Empresa;
use App\Models\Orcamento;
use App\Models\User;
use App\Services\Financeiro\GeradorCobrancaOrcamento;
use App\Services\MovimentacaoFinanceiraService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceiroController extends Controller
{
    /**
     * Agenda a cobrança de um orçamento
     */
    public function agendarCobranca(Request $request, Orcamento $orcamento)
    {
        $request->validate([
            'data_agendamento' => 'required|date|after_or_equal:today',
        ]);

        $orcamento->update([
            'data_agendamento' => $request->data_agendamento,
        ]);

        return redirect()
            ->route('financeiro.cobrar')
            ->with('success', 'Cobrança agendada para '.Carbon::parse($orcamento->data_agendamento)->format('d/m/Y'));
    }

    /**
     * Cancela o agendamento de cobrança de um orçamento
     */
    public function cancelarAgendamento(Request $request, Orcamento $orcamento)
    {
        $orcamento->update(['data_agendamento' => null]);

        return redirect()
            ->route('financeiro.cobrar')
            ->with('success', 'Agendamento cancelado com sucesso!');
    }

    /**
     * Lista orçamentos no financeiro
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        $hoje = Carbon::today();

        $kpisGerais = [
            'total_receber' => Cobranca::where('status', '!=', 'pago')->sum('valor'),
            'total_pago' => Cobranca::where('status', 'pago')->sum('valor'),
            'total_vencido' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '<', $hoje)
                ->sum('valor'),
            'vence_hoje' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', $hoje)
                ->sum('valor'),
        ];

        $query = Orcamento::with(['cliente', 'preCliente', 'empresa'])
            ->where('status', 'financeiro');

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', $search)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nome_fantasia', 'like', $search))
                    ->orWhereHas('preCliente', fn ($p) => $p->where('nome_fantasia', 'like', $search));
            });
        }

        if ($request->filled('empresa_id')) {
            $query->whereIn('empresa_id', (array) $request->empresa_id);
        }

        $totalGeralFiltrado = (clone $query)->sum('valor_total');

        $contadoresStatus = [
            'financeiro' => (clone $query)->count(),
        ];

        $orcamentos = $query->orderByDesc('updated_at')->paginate(10)->withQueryString();
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();

        return view('financeiro.index', compact(
            'kpisGerais',
            'orcamentos',
            'empresas',
            'contadoresStatus',
            'totalGeralFiltrado'
        ));
    }

    /**
     * Gera cobranças de um orçamento
     */
    public function gerarCobranca(Request $request, Orcamento $orcamento)
    {
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403
        );

        try {
            DB::transaction(function () use ($request, $orcamento) {

                $orcamento = Orcamento::lockForUpdate()->findOrFail($orcamento->id);

                if ($orcamento->status !== 'financeiro') {
                    throw ValidationException::withMessages([
                        'orcamento' => 'Este orçamento já foi processado.',
                    ]);
                }

                $dados = $request->validate([
                    'forma_pagamento' => 'required|in:pix,debito,credito,boleto,faturado',
                    'parcelas' => 'required_if:forma_pagamento,credito,boleto,faturado|integer|min:1|max:12',
                    'vencimentos' => 'required_if:forma_pagamento,credito,boleto,faturado|array|min:1|max:12',
                    'vencimentos.*' => 'date',
                    'valores_parcelas' => 'nullable|array',
                    'valores_parcelas.*' => 'numeric|min:0.01',
                ]);

                if (! empty($dados['valores_parcelas']) && $dados['forma_pagamento'] !== 'pix') {
                    if (abs(array_sum($dados['valores_parcelas']) - $orcamento->valor_total) > 0.02) {
                        throw ValidationException::withMessages([
                            'valores_parcelas' => 'Soma das parcelas diferente do valor total.',
                        ]);
                    }
                }

                $gerador = new GeradorCobrancaOrcamento($orcamento, $dados);
                $parcelas = $gerador->gerar();

                if (! $parcelas) {
                    throw new \Exception('Nenhuma parcela gerada.');
                }

                foreach ($parcelas as $i => $parcela) {
                    Cobranca::create([
                        'orcamento_id' => $orcamento->id,
                        'cliente_id' => $parcela['cliente_id'] ?? $orcamento->cliente_id,
                        'descricao' => $parcela['descricao'],
                        'valor' => $parcela['valor'],
                        'data_vencimento' => $parcela['data_vencimento'],
                        'status' => 'pendente',
                        'origem' => 'orcamento',
                        'forma_pagamento' => $dados['forma_pagamento'],
                        'parcela_num' => $i + 1,
                        'parcelas_total' => count($parcelas),
                    ]);
                }

                $orcamento->update(['status' => 'aguardando_pagamento']);
            });

            return redirect()->route('financeiro.cobrar')
                ->with('success', 'Cobrança gerada com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Ajuste direto de saldo bancário
     */
    public function ajusteManual(Request $request)
    {
        $data = $request->validate([
            'conta_id' => 'required|exists:contas_financeiras,id',
            'valor' => 'required|string',
            'data' => 'required|date',
            'observacao' => 'nullable|string|max:255',
        ]);

        $conta = ContaFinanceira::findOrFail($data['conta_id']);
        $saldoAtual = (float) $conta->saldo;
        $valorInformado = (float) str_replace(',', '.', str_replace('.', '', $data['valor']));
        $diferenca = $valorInformado - $saldoAtual;

        if ($diferenca == 0) {
            return redirect()
                ->route('financeiro.movimentacao', ['conta' => $conta->id])
                ->with('info', 'O saldo informado é igual ao saldo atual. Nenhum ajuste necessário.');
        }

        $tipoAjuste = $diferenca > 0 ? 'ajuste_entrada' : 'ajuste_saida';
        $valorAjuste = abs($diferenca);

        $observacao = sprintf(
            'Ajuste de saldo: de R$ %s para R$ %s (%sR$ %s)',
            number_format($saldoAtual, 2, ',', '.'),
            number_format($valorInformado, 2, ',', '.'),
            $diferenca > 0 ? '+' : '-',
            number_format($valorAjuste, 2, ',', '.')
        );

        if (! empty($data['observacao'])) {
            $observacao .= ' - '.$data['observacao'];
        }

        (new MovimentacaoFinanceiraService)->registrar([
            'conta_origem_id' => $data['conta_id'],
            'conta_destino_id' => $data['conta_id'],
            'tipo' => $tipoAjuste,
            'valor' => $diferenca,
            'observacao' => $observacao,
            'user_id' => auth()->id(),
            'data_movimentacao' => $data['data'],
        ]);

        return redirect()
            ->route('financeiro.movimentacao', ['conta' => $conta->id])
            ->with('success', 'Ajuste manual realizado com sucesso! Saldo ajustado de R$ '.number_format($saldoAtual, 2, ',', '.').' para R$ '.number_format($valorInformado, 2, ',', '.'));
    }

    public function cobrar(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        // ================= KPIs GERAIS =================
        $hoje = Carbon::today();

        $kpisGerais = [
            'total_receber' => Cobranca::where('status', '!=', 'pago')->sum('valor'),
            'total_pago' => Cobranca::where('status', 'pago')->sum('valor'),
            'total_vencido' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '<', $hoje)
                ->sum('valor'),
            'vence_hoje' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', $hoje)
                ->sum('valor'),
        ];

        // ================= QUERY BASE =================
        $query = Orcamento::with(['cliente', 'preCliente', 'empresa'])
            ->where('status', 'financeiro');

        // ================= FILTRO DE BUSCA =================
        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', $search)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nome_fantasia', 'like', $search))
                    ->orWhereHas('preCliente', fn ($p) => $p->where('nome_fantasia', 'like', $search));
            });
        }

        // ================= FILTRO DE EMPRESA =================
        if ($request->filled('empresa_id')) {
            $query->whereIn('empresa_id', (array) $request->empresa_id);
        }

        // ================= TOTAIS =================
        $totalGeralFiltrado = (clone $query)->sum('valor_total');

        $contadoresStatus = [
            'financeiro' => (clone $query)->count(),
        ];

        // ================= PAGINAÇÃO =================
        $orcamentos = $query
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $empresas = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        return view('financeiro.cobrar', compact(
            'kpisGerais',
            'orcamentos',
            'empresas',
            'contadoresStatus',
            'totalGeralFiltrado'
        ));
    }
}
