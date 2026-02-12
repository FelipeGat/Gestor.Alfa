<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Cobranca;
use App\Models\ContaPagar;
use App\Models\Empresa;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\Fornecedor;

class RelatorioFinanceiroController extends Controller
{
    public function contasReceberPagar(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $clienteId = $request->cliente_id;
        $fornecedorId = $request->fornecedor_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusReceber = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $applyStatusPagar = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryReceber = Cobranca::with([
            'cliente',
            'orcamento.empresa',
            'contaFixa.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFixa', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($status, function ($q) use ($applyStatusReceber) {
                $applyStatusReceber($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalReceber = (clone $queryReceber)->sum('valor');

        $contasReceber = $queryReceber
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'receber_page')
            ->withQueryString();

        $queryPagar = ContaPagar::with([
            'fornecedor',
            'centroCusto',
            'orcamento.empresa',
            'contaFinanceira.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFinanceira', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, fn($q) => $q->where('centro_custo_id', $centroCustoId))
            ->when($fornecedorId, fn($q) => $q->where('fornecedor_id', $fornecedorId))
            ->when($status, function ($q) use ($applyStatusPagar) {
                $applyStatusPagar($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalPagar = (clone $queryPagar)->sum('valor');

        $contasPagar = $queryPagar
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'pagar_page')
            ->withQueryString();

        $resultado = $totalReceber - $totalPagar;

        $empresas = Empresa::orderBy('nome_fantasia')->orderBy('razao_social')->get();
        $centrosCusto = CentroCusto::orderBy('nome')->get();
        $clientes = Cliente::orderBy('nome_fantasia')->orderBy('razao_social')->orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->orderBy('razao_social')->get();

        return view('relatorios.relatorio-contas-receber-pagar', compact(
            'contasReceber',
            'contasPagar',
            'totalReceber',
            'totalPagar',
            'resultado',
            'dataInicio',
            'dataFim',
            'empresas',
            'centrosCusto',
            'clientes',
            'fornecedores'
        ));
    }
}
