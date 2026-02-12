<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $queryReceber = Cobranca::with([
            'cliente',
            'orcamento.empresa',
            'orcamento.centroCusto',
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
            ->when($centroCustoId, function ($q) use ($centroCustoId) {
                $q->whereHas('orcamento', function ($oq) use ($centroCustoId) {
                    $oq->where('centro_custo_id', $centroCustoId);
                });
            })
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($dataInicio && $dataFim, fn($q) =>
                $q->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            );

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
            ->when($dataInicio && $dataFim, fn($q) =>
                $q->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            );

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
            'empresas',
            'centrosCusto',
            'clientes',
            'fornecedores'
        ));
    }
}
