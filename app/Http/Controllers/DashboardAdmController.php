<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PDF;

class DashboardAdmController extends Controller
{
    public function index(Request $request)
    {
        // ================= FILTROS =================
        $filtroRapido = $request->get('filtro_rapido', 'mes');

        // Processar filtro de data
        $inicio = null;
        $fim = null;

        switch ($filtroRapido) {
            case 'dia':
                $inicio = now()->startOfDay();
                $fim = now()->endOfDay();
                break;
            case 'semana':
                $inicio = now()->startOfWeek();
                $fim = now()->endOfWeek();
                break;
            case 'mes':
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
                break;
            case 'mes_anterior':
                $inicio = now()->subMonth()->startOfMonth();
                $fim = now()->subMonth()->endOfMonth();
                break;
            case 'ano':
                $inicio = now()->startOfYear();
                $fim = now()->endOfYear();
                break;
            case 'custom':
                $inicio = $request->get('data_inicio') ? Carbon::parse($request->get('data_inicio'))->startOfDay() : now()->startOfMonth();
                $fim = $request->get('data_fim') ? Carbon::parse($request->get('data_fim'))->endOfDay() : now()->endOfMonth();
                break;
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        $dataInicio = $inicio;
        $dataFim = $fim;

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

        $statusCount = (clone $queryCards)
            ->select('status_atual', DB::raw('COUNT(*) as total'))
            ->groupBy('status_atual')
            ->pluck('total', 'status_atual');

        $chamadosAbertos = $statusCount->get('aberto', 0);
        $chamadosEmAtendimento = $statusCount->get('em_atendimento', 0);
        $chamadosConcluidos = $statusCount->get('concluido', 0);
        $chamadosAguardando = $statusCount->get('aguardando_cliente', 0);
        $chamadosCancelados = $statusCount->get('cancelado', 0);

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
            'inicio',
            'fim',
            'filtroRapido',
            'empresaId',
            'statusFiltro',
            'empresas',
            'todosStatus',
            'metricasFiltradas',
            'chamadosAbertos',
            'chamadosEmAtendimento',
            'chamadosConcluidos',
            'chamadosAguardando',
            'chamadosCancelados',
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

    /**
     * Retorna atendimentos via AJAX (para o modal)
     */
    public function getAtendimentos(Request $request)
    {
        $empresaId = $request->get('empresa_id');
        $statusFiltro = $request->get('status_atual');
        $filtroRapido = $request->get('filtro_rapido', 'mes');
        $inicioCustom = $request->get('inicio_custom');
        $fimCustom = $request->get('fim_custom');

        // Calcula período
        $hoje = Carbon::now('America/Sao_Paulo');
        switch ($filtroRapido) {
            case 'dia':
                $inicio = $hoje->copy()->startOfDay();
                $fim = $hoje->copy()->endOfDay();
                break;
            case 'semana':
                $inicio = $hoje->copy()->startOfWeek();
                $fim = $hoje->copy()->endOfWeek();
                break;
            case 'mes':
                $inicio = $hoje->copy()->startOfMonth();
                $fim = $hoje->copy()->endOfMonth();
                break;
            case 'mes_anterior':
                $inicio = $hoje->copy()->subMonth()->startOfMonth();
                $fim = $hoje->copy()->subMonth()->endOfMonth();
                break;
            case 'ano':
                $inicio = $hoje->copy()->startOfYear();
                $fim = $hoje->copy()->endOfYear();
                break;
            case 'custom':
                $inicio = $inicioCustom ? Carbon::parse($inicioCustom) : $hoje->copy()->startOfMonth();
                $fim = $fimCustom ? Carbon::parse($fimCustom) : $hoje->copy()->endOfMonth();
                break;
            default:
                $inicio = $hoje->copy()->startOfMonth();
                $fim = $hoje->copy()->endOfMonth();
        }

        // Busca atendimentos
        $query = Atendimento::with(['cliente:id,nome_fantasia', 'empresa:id,nome_fantasia', 'funcionario:id,nome'])
            ->whereBetween('data_atendimento', [$inicio, $fim]);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        if ($statusFiltro) {
            $query->where('status_atual', $statusFiltro);
        }

        $atendimentos = $query->orderBy('data_atendimento', 'desc')->get()->map(function ($atendimento) {
            return [
                'numero' => $atendimento->numero_atendimento,
                'cliente' => $atendimento->cliente->nome_fantasia ?? 'N/A',
                'empresa' => $atendimento->empresa->nome_fantasia ?? 'N/A',
                'tecnico' => $atendimento->funcionario->nome ?? 'Não atribuído',
                'descricao' => Str::limit($atendimento->descricao, 50),
                'status_atual' => $atendimento->status_atual,
                'data_atendimento' => Carbon::parse($atendimento->data_atendimento)->format('d/m/Y'),
                'url' => route('atendimentos.edit', $atendimento->id)
            ];
        });

        return response()->json([
            'success' => true,
            'atendimentos' => $atendimentos,
            'total' => $atendimentos->count()
        ]);
    }

    /**
     * Exporta dashboard técnico em PDF
     */
    public function exportar(Request $request)
    {
        $empresaId = $request->get('empresa_id');
        $statusFiltro = $request->get('status_atual');
        $filtroRapido = $request->get('filtro_rapido', 'mes');
        $inicioCustom = $request->get('inicio_custom');
        $fimCustom = $request->get('fim_custom');

        // Calcula período
        $hoje = Carbon::now('America/Sao_Paulo');
        switch ($filtroRapido) {
            case 'dia':
                $inicio = $hoje->copy()->startOfDay();
                $fim = $hoje->copy()->endOfDay();
                break;
            case 'semana':
                $inicio = $hoje->copy()->startOfWeek();
                $fim = $hoje->copy()->endOfWeek();
                break;
            case 'mes':
                $inicio = $hoje->copy()->startOfMonth();
                $fim = $hoje->copy()->endOfMonth();
                break;
            case 'mes_anterior':
                $inicio = $hoje->copy()->subMonth()->startOfMonth();
                $fim = $hoje->copy()->subMonth()->endOfMonth();
                break;
            case 'ano':
                $inicio = $hoje->copy()->startOfYear();
                $fim = $hoje->copy()->endOfYear();
                break;
            case 'custom':
                $inicio = $inicioCustom ? Carbon::parse($inicioCustom) : $hoje->copy()->startOfMonth();
                $fim = $fimCustom ? Carbon::parse($fimCustom) : $hoje->copy()->endOfMonth();
                break;
            default:
                $inicio = $hoje->copy()->startOfMonth();
                $fim = $hoje->copy()->endOfMonth();
        }

        // Busca atendimentos
        $query = Atendimento::with(['cliente:id,nome_fantasia', 'empresa:id,nome_fantasia', 'funcionario:id,nome'])
            ->whereBetween('data_atendimento', [$inicio, $fim]);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        if ($statusFiltro) {
            $query->where('status_atual', $statusFiltro);
        }

        $atendimentos = $query->orderBy('data_atendimento', 'desc')->get();
        $total = $atendimentos->count();

        // Informações para o cabeçalho
        $periodoTexto = $this->getPeriodoTexto($filtroRapido, $inicio, $fim);
        $empresaNome = $empresaId ? Empresa::find($empresaId)?->nome_fantasia : 'Todas';
        $statusTexto = $statusFiltro ? $this->getStatusLabel($statusFiltro) : 'Todos';

        $pdf = PDF::loadView('dashboard.exportar', compact(
            'atendimentos',
            'total',
            'periodoTexto',
            'empresaNome',
            'statusTexto',
            'inicio',
            'fim'
        ));

        $nomeArquivo = 'dashboard-tecnico-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->download($nomeArquivo);
    }

    /**
     * Retorna texto formatado do período
     */
    private function getPeriodoTexto($filtro, $inicio, $fim)
    {
        switch ($filtro) {
            case 'dia':
                return 'Hoje (' . $inicio->format('d/m/Y') . ')';
            case 'semana':
                return 'Esta Semana (' . $inicio->format('d/m') . ' a ' . $fim->format('d/m/Y') . ')';
            case 'mes':
                return 'Este Mês (' . $inicio->format('m/Y') . ')';
            case 'mes_anterior':
                return 'Mês Anterior (' . $inicio->format('m/Y') . ')';
            case 'ano':
                return 'Este Ano (' . $inicio->format('Y') . ')';
            case 'custom':
                return $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');
            default:
                return $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');
        }
    }

    /**
     * Retorna label formatado do status
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'aberto' => 'Aberto',
            'em_atendimento' => 'Em Atendimento',
            'aguardando_cliente' => 'Aguardando Cliente',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado'
        ];
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
