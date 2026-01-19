<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\Assunto;
use App\Models\Atendimento;
use Illuminate\Support\Facades\DB;
use App\Models\Orcamento;

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

        /* ================= ATENDIMENTOS ================= */

        // Total de Chamados
        $totalChamados = Atendimento::count();

        // Chamados em Aberto
        $chamadosAbertos = Atendimento::where('status_atual', 'aberto')->count();

        // Chamados em Atendimento
        $chamadosEmAtendimento = Atendimento::where('status_atual', 'em_atendimento')->count();

        // Chamados Concluído
        $chamadosConcluidos = Atendimento::where('status_atual', 'concluido')->count();

        // Chamados por Empresa (para gráfico)
        $chamadosPorEmpresa = Atendimento::select('empresa_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('empresa_id')
            ->groupBy('empresa_id')
            ->with('empresa:id,nome_fantasia,razao_social')
            ->get();

        $labelsChamadosEmpresa = $chamadosPorEmpresa->map(function ($item) {
            return $item->empresa->nome_fantasia ?? $item->empresa->razao_social ?? 'Sem Empresa';
        });

        $valoresChamadosEmpresa = $chamadosPorEmpresa->pluck('total');

        // Chamados por Status (para gráfico)
        $chamadosPorStatus = Atendimento::select('status_atual', DB::raw('COUNT(*) as total'))
            ->whereNotNull('status_atual')
            ->groupBy('status_atual')
            ->get();

        $labelsChamadosStatus = $chamadosPorStatus->map(function ($item) {
            return ucfirst(str_replace('_', ' ', $item->status_atual));
        });

        $valoresChamadosStatus = $chamadosPorStatus->pluck('total');

        // Chamados por Prioridade (para gráfico)
        $chamadosPorPrioridade = Atendimento::select('prioridade', DB::raw('COUNT(*) as total'))
            ->whereNotNull('prioridade')
            ->groupBy('prioridade')
            ->get();

        $labelsChamadosPrioridade = $chamadosPorPrioridade->map(function ($item) {
            return ucfirst($item->prioridade);
        });

        $valoresChamadosPrioridade = $chamadosPorPrioridade->pluck('total');

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
            'valoresCategoria',
            'totalChamados',
            'chamadosAbertos',
            'chamadosEmAtendimento',
            'chamadosConcluidos',
            'labelsChamadosEmpresa',
            'valoresChamadosEmpresa',
            'labelsChamadosStatus',
            'valoresChamadosStatus',
            'labelsChamadosPrioridade',
            'valoresChamadosPrioridade'
        ));
    }

    public function comercial()
    {
        // Orçamentos por Status
        $orcamentosPorStatus = Orcamento::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Orçamentos por Empresa (valor total)
        $orcamentosPorEmpresa = Orcamento::select(
                'empresa_id',
                DB::raw('SUM(valor_total) as total_valor'),
                DB::raw('COUNT(*) as total_qtd')
            )
            ->whereNotNull('valor_total')
            ->groupBy('empresa_id')
            ->with('empresa')
            ->get();

        // Conversão
        $aprovados = Orcamento::where('status', 'aprovado')->count();
        $recusados = Orcamento::where('status', 'recusado')->count();

        return view('dashboard-comercial.index', compact(
            'orcamentosPorStatus',
            'orcamentosPorEmpresa',
            'aprovados',
            'recusados'
        ));
    }

}