<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orcamento;

class FinanceiroOrcamentoController extends Controller
{
    public function orcamentosPorCliente($cliente_id)
    {
        $orcamentos = Orcamento::where('cliente_id', $cliente_id)
            ->whereIn('status', ['aprovado', 'em_andamento'])
            ->select('id', 'numero_orcamento', 'descricao', 'status', 'valor_total')
            ->get();

        return response()->json($orcamentos);
    }
}
