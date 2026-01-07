<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $mes = now()->month;
        $ano = now()->year;

        // Clientes
        $totalClientes = Cliente::count();
        $clientesAtivos = Cliente::where('ativo', true)->count();
        $clientesInativos = Cliente::where('ativo', false)->count();

        // Financeiro
        $receitaPrevista = Cobranca::whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->where('status', '!=', 'pago')
            ->sum('valor');

        $receitaRealizada = Cobranca::whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->where('status', 'pago')
            ->sum('valor');

        // Cobranças
        $clientesComCobranca = Cobranca::distinct('cliente_id')->count('cliente_id');

        $clientesComBoletoNaoBaixado = Boleto::whereNull('baixado_em')
            ->distinct('cliente_id')
            ->count('cliente_id');

        return view('dashboard', compact(
            'totalClientes',
            'clientesAtivos',
            'clientesInativos',
            'receitaPrevista',
            'receitaRealizada',
            'clientesComCobranca',
            'clientesComBoletoNaoBaixado'
        ));
    }
}