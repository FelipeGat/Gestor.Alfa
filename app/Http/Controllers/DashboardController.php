<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\Assunto;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $mes = now()->month;
        $ano = now()->year;

        /* ================= CLIENTES ================= */
        $totalClientes     = Cliente::count();
        $clientesAtivos    = Cliente::where('ativo', true)->count();
        $clientesInativos  = Cliente::where('ativo', false)->count();
        $clientesContrato  = Cliente::where('tipo_cliente', 'CONTRATO')->count();
        $clientesAvulso    = Cliente::where('tipo_cliente', 'AVULSO')->count();

        /* ================= FINANCEIRO ================= */
        $receitaPrevista = Cobranca::whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->where('status', '!=', 'pago')
            ->sum('valor');

        $receitaRealizada = Cobranca::whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->where('status', 'pago')
            ->sum('valor');

        /* ================= COBRANÇAS ================= */
        $clientesComCobranca = Cobranca::distinct('cliente_id')->count('cliente_id');

        $clientesComBoletoNaoBaixado = Boleto::whereNull('baixado_em')
            ->distinct('cliente_id')
            ->count('cliente_id');

        /* ================= ASSUNTOS ================= */

        // Assuntos por Empresa
        $assuntosPorEmpresa = Assunto::select('empresa_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('empresa_id')
            ->groupBy('empresa_id')
            ->with('empresa:id,nome_fantasia,razao_social')
            ->get();

        $labelsEmpresa = $assuntosPorEmpresa->map(function ($item) {
            return $item->empresa->nome_fantasia ?? $item->empresa->razao_social;
        });

        $valoresEmpresa = $assuntosPorEmpresa->pluck('total');

        // Serviço x Venda x Administrativo x Comercial
        $assuntosPorTipo = Assunto::select('tipo', DB::raw('COUNT(*) as total'))
            ->whereNotNull('tipo')
            ->groupBy('tipo')
            ->get();

        $labelsTipo  = $assuntosPorTipo->pluck('tipo');
        $valoresTipo = $assuntosPorTipo->pluck('total');

        // Top 5 Categorias
        $topCategorias = Assunto::select('categoria', DB::raw('COUNT(*) as total'))
            ->whereNotNull('categoria')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $labelsCategoria  = $topCategorias->pluck('categoria');
        $valoresCategoria = $topCategorias->pluck('total');

        return view('dashboard', compact(
            'totalClientes',
            'clientesAtivos',
            'clientesInativos',
            'clientesContrato',
            'clientesAvulso',
            'receitaPrevista',
            'receitaRealizada',
            'clientesComCobranca',
            'clientesComBoletoNaoBaixado',
            'labelsEmpresa',
            'valoresEmpresa',
            'labelsTipo',
            'valoresTipo',
            'labelsCategoria',
            'valoresCategoria'
        ));
    }
}