<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardAdmController extends Controller
{
    public function index(Request $request)
    {
        // ================= FILTROS =================
        $dataFim = $request->input('data_fim') ? Carbon::parse($request->input('data_fim')) : Carbon::today();
        $dataInicio = $request->input('data_inicio') ? Carbon::parse($request->input('data_inicio')) : $dataFim->copy()->subDays(29);

        $empresaId = $request->input('empresa_id');
        $statusFiltro = $request->input('status_atual');

        // Query base de atendimentos para o período
        $atendimentosNoPeriodo = Atendimento::whereBetween('data_atendimento', [$dataInicio, $dataFim]);

        if ($empresaId) {
            $atendimentosNoPeriodo->where('empresa_id', $empresaId);
        }
        if ($statusFiltro) {
            $atendimentosNoPeriodo->where('status_atual', $statusFiltro);
        }

        // ================= DADOS PARA OS FILTROS DO CABEÇALHO =================
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();
        $todosStatus = ['aberto', 'em_atendimento', 'aguardando_cliente', 'concluido', 'cancelado'];

        // ================= MÉTRICAS DE RESUMO =================
        $metricasFiltradas = (clone $atendimentosNoPeriodo)->select(DB::raw('COUNT(*) as qtd'))->first();

        // ================= CARDS DE STATUS GLOBAIS =================
        $queryCards = Atendimento::whereBetween('data_atendimento', [$dataInicio, $dataFim]);
        if ($empresaId) {
            $queryCards->where('empresa_id', $empresaId);
        }
        $chamadosAbertos    = (clone $queryCards)->where('status_atual', 'aberto')->count();
        $chamadosEmAtendimento = (clone $queryCards)->where('status_atual', 'em_atendimento')->count();
        $chamadosConcluidos = (clone $queryCards)->where('status_atual', 'concluido')->count();
        $chamadosAguardando = (clone $queryCards)->where('status_atual', 'aguardando_cliente')->count();

        // ================= GRÁFICOS =================

        // Gráfico: Atendimentos por Dia
        $atendimentosPorDia = (clone $atendimentosNoPeriodo)
            ->select(DB::raw('DATE(data_atendimento) as data'), DB::raw('COUNT(*) as total'))
            ->groupBy('data')->orderBy('data', 'asc')->get();
        $labelsAtendimentosDia = $atendimentosPorDia->pluck('data')->map(fn($d) => Carbon::parse($d)->format('d/m'));
        $valoresAtendimentosDia = $atendimentosPorDia->pluck('total');

        // Gráfico: Atendimentos por Técnico (CORRIGIDO)
        $atendimentosPorTecnico = (clone $atendimentosNoPeriodo)
            ->whereNotNull('funcionario_id')
            ->with('funcionario:id,nome')
            ->select('funcionario_id', DB::raw('COUNT(*) as total'))
            ->groupBy('funcionario_id')->orderBy('total', 'desc')->get();

        $labelsAtendimentosTecnico = $atendimentosPorTecnico->map(fn($item) => $item->funcionario->nome ?? 'Funcionário não identificado');
        $valoresAtendimentosTecnico = $atendimentosPorTecnico->pluck('total');

        // Gráfico: Atendimentos por Status
        $atendimentosPorStatus = (clone $atendimentosNoPeriodo)
            ->select('status_atual', DB::raw('COUNT(*) as total'))
            ->groupBy('status_atual')->get();
        $labelsAtendimentosStatus = $atendimentosPorStatus->map(fn($i) => ucfirst(str_replace('_', ' ', $i->status_atual)));
        $valoresAtendimentosStatus = $atendimentosPorStatus->pluck('total');

        // Tabela: Cliente que mais abre atendimento (Top 5)
        $topClientes = (clone $atendimentosNoPeriodo)
            ->whereNotNull('cliente_id')->with('cliente:id,nome_fantasia')
            ->select('cliente_id', DB::raw('COUNT(*) as total'))
            ->groupBy('cliente_id')->orderBy('total', 'desc')->limit(5)->get();

        // Gráfico: Assunto que mais abre atendimento (Top 5)
        $topAssuntos = (clone $atendimentosNoPeriodo)
            ->whereNotNull('assunto_id')
            ->with('assunto:id,nome')
            ->select('assunto_id', DB::raw('COUNT(*) as total'))
            ->groupBy('assunto_id')->orderBy('total', 'desc')->limit(5)->get();

        $labelsTopAssuntos = $topAssuntos->map(fn($item) => $item->assunto->nome ?? 'Assunto não identificado');
        $valoresTopAssuntos = $topAssuntos->pluck('total');

        // Gráfico: Chamados por Prioridade
        $chamadosPorPrioridade = (clone $atendimentosNoPeriodo)
            ->select('prioridade', DB::raw('COUNT(*) as total'))
            ->whereNotNull('prioridade')->groupBy('prioridade')->get();
        $labelsChamadosPrioridade = $chamadosPorPrioridade->map(fn($i) => ucfirst($i->prioridade));
        $valoresChamadosPrioridade = $chamadosPorPrioridade->pluck('total');

        // Gráfico: Chamados por Empresa
        $labelsChamadosEmpresa = [];
        $valoresChamadosEmpresa = [];
        if (!$empresaId) {
            $chamadosPorEmpresaData = (clone $atendimentosNoPeriodo)
                ->whereNotNull('empresa_id')->with('empresa:id,nome_fantasia')
                ->select('empresa_id', DB::raw('COUNT(*) as total'))
                ->groupBy('empresa_id')->orderBy('total', 'desc')->limit(10)->get();
            $labelsChamadosEmpresa = $chamadosPorEmpresaData->map(fn($i) => $i->empresa->nome_fantasia ?? 'N/A');
            $valoresChamadosEmpresa = $chamadosPorEmpresaData->pluck('total');
        }

        // Retorna a view com todas as variáveis
        return view('dashboard', compact(
            'dataInicio',
            'dataFim',
            'empresaId',
            'statusFiltro',
            'empresas',
            'todosStatus',
            'metricasFiltradas',
            'chamadosAbertos',
            'chamadosEmAtendimento',
            'chamadosConcluidos',
            'chamadosAguardando',
            'labelsAtendimentosDia',
            'valoresAtendimentosDia',
            'labelsAtendimentosTecnico',
            'valoresAtendimentosTecnico',
            'labelsAtendimentosStatus',
            'valoresAtendimentosStatus',
            'topClientes',
            'labelsTopAssuntos',
            'valoresTopAssuntos',
            'labelsChamadosPrioridade',
            'valoresChamadosPrioridade',
            'labelsChamadosEmpresa',
            'valoresChamadosEmpresa'
        ));
    }
}
