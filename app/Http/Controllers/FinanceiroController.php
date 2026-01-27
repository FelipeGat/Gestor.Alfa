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
use App\Services\Financeiro\GeradorCobrancaOrcamento;
use Illuminate\Validation\ValidationException;


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
    | GERAR COBRANÇA (ORÇAMENTO → COM MODAL PARA CONTAS A RECEBER)
    |--------------------------------------------------------------------------
    */
    public function gerarCobranca(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        try {

            // Garante dados atualizados
            $orcamento = Orcamento::lockForUpdate()->findOrFail($orcamento->id);

            if ($orcamento->status !== 'financeiro') {
                throw ValidationException::withMessages([
                    'orcamento' => 'Este orçamento já foi processado pelo financeiro.'
                ]);
            }

            // Dados vindos do modal
            // Não exigir parcelas/vencimentos para formas de pagamento à vista (pix, debito)
            $dados = $request->validate([
                'forma_pagamento' => 'required|in:pix,debito,credito,boleto,faturado',
                'parcelas'        => 'required_if:forma_pagamento,credito,boleto,faturado|integer|min:1',
                'vencimentos'     => 'required_if:forma_pagamento,credito,boleto,faturado|array|min:1',
                'vencimentos.*'   => 'required_if:forma_pagamento,credito,boleto,faturado|date',
            ]);

            // CHAMADA DO SERVICE QUE GERA AS PARCELAS)
            $gerador = new GeradorCobrancaOrcamento($orcamento, $dados);

            $parcelasGeradas = $gerador->gerar();

            if (empty($parcelasGeradas)) {
                throw new \Exception('Nenhuma parcela foi gerada.');
            }

            DB::transaction(function () use ($parcelasGeradas, $orcamento, $dados) {

                $totalParcelas = count($parcelasGeradas);

                foreach ($parcelasGeradas as $index => $parcela) {
                    Cobranca::create([
                        'orcamento_id'    => $orcamento->id,
                        'cliente_id'      => $parcela['cliente_id'] ?? $orcamento->cliente_id,
                        'descricao'       => $parcela['descricao'],
                        'valor'           => $parcela['valor'],
                        'data_vencimento' => $parcela['data_vencimento'],
                        'status'          => 'pendente',
                        'origem'          => $parcela['origem'] ?? 'orcamento',
                        'forma_pagamento' => $dados['forma_pagamento'] ?? null,
                        'parcela_num'     => $index + 1,
                        'parcelas_total'  => $totalParcelas,
                    ]);
                }

                $orcamento->update([
                    'status' => 'aguardando_pagamento',
                ]);
            });

            return redirect()
                ->route('financeiro.contasareceber')
                ->with('success', 'Cobrança(s) gerada(s) com sucesso.');
        } catch (ValidationException $e) {

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Erro ao gerar cobrança: ' . $e->getMessage());
        }
    }
}
