<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardTecnicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario_id;

        $periodo = $request->query("periodo", "todos");

        // Builder base com filtro de funcionário
        $queryBase = Atendimento::where("funcionario_id", $funcionarioId);

        // Aplicar filtro de período
        if ($periodo === "hoje") {
            $queryBase->whereDate("data_atendimento", Carbon::today());
        } elseif ($periodo === "semana") {
            $queryBase->whereBetween("data_atendimento", [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        } elseif ($periodo === "mes") {
            $queryBase->whereMonth("data_atendimento", Carbon::now()->month)
                ->whereYear("data_atendimento", Carbon::now()->year);
        }
        // 'todos' não aplica filtro de data

        // Status válidos: aberto, em_atendimento, finalizacao, concluido, cancelado
        // KPIs - Sempre filtra por funcionario_id e período selecionado
        $pendentes = (clone $queryBase)
            ->where("status_atual", "aberto")
            ->count();

        $emAndamento = (clone $queryBase)
            ->where("status_atual", "em_atendimento")
            ->count();

        $concluidos = (clone $queryBase)
            ->where("status_atual", "concluido")
            ->count();

        $cancelados = (clone $queryBase)
            ->where("status_atual", "cancelado")
            ->count();

        $total = $pendentes + $emAndamento + $concluidos + $cancelados;

        $concluidosHoje = Atendimento::where("status_atual", "concluido")
            ->where("funcionario_id", $funcionarioId)
            ->whereDate("updated_at", Carbon::today())
            ->count();

        $horasTrabalhadasHoje = $concluidosHoje * 1.5;
        $horasTrabalhadasSemana = $concluidos * 1.5;

        // Atendimento em andamento (com filtro de período)
        $atendimentoEmAndamento = (clone $queryBase)
            ->with(["cliente"])
            ->where("status_atual", "em_atendimento")
            ->first();

        // Próximos atendimentos
        $proximosAtendimentos = collect();

        $periodoLabel = $periodo === "hoje" ? "Hoje" : ($periodo === "semana" ? "Esta Semana" : ($periodo === "mes" ? "Este Mês" : "Todos"));

        return response()->json([
            "data" => [
                "periodo_selecionado" => $periodo,
                "periodo_label" => $periodoLabel,
                "kpis" => [
                    "total" => $total,
                    "concluidos" => $concluidos,
                    "pendentes" => $pendentes,
                    "cancelados" => $cancelados,
                    "em_andamento" => $emAndamento,
                    "horas_trabalhadas_hoje" => $horasTrabalhadasHoje,
                    "horas_trabalhadas_semana" => $horasTrabalhadasSemana,
                ],
                "atendimento_em_andamento" => $atendimentoEmAndamento ? [
                    "id" => $atendimentoEmAndamento->id,
                    "status" => $atendimentoEmAndamento->status_atual,
                    "cliente" => $atendimentoEmAndamento->cliente ? [
                        "id" => $atendimentoEmAndamento->cliente->id,
                        "nome" => $atendimentoEmAndamento->cliente->nome,
                    ] : null,
                    "tempo_executado" => $atendimentoEmAndamento->tempo_executado ?? 0,
                ] : null,
                "proximos_atendimentos" => $proximosAtendimentos->toArray(),
            ]
        ]);
    }
}
