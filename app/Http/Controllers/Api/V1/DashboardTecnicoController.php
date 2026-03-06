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

        // Status válidos: aberto, em_atendimento, finalizacao, concluido, cancelado
        // KPIs - Sempre filtra por funcionario_id
        $pendentes = Atendimento::where("status_atual", "aberto")
            ->where("funcionario_id", $funcionarioId)
            ->count();

        $emAndamento = Atendimento::where("status_atual", "em_atendimento")
            ->where("funcionario_id", $funcionarioId)
            ->count();

        $concluidos = Atendimento::where("status_atual", "concluido")
            ->where("funcionario_id", $funcionarioId)
            ->count();

        $cancelados = Atendimento::where("status_atual", "cancelado")
            ->where("funcionario_id", $funcionarioId)
            ->count();

        $total = $pendentes + $emAndamento + $concluidos + $cancelados;

        $concluidosHoje = Atendimento::where("status_atual", "concluido")
            ->where("funcionario_id", $funcionarioId)
            ->whereDate("updated_at", Carbon::today())
            ->count();

        $horasTrabalhadasHoje = $concluidosHoje * 1.5;
        $horasTrabalhadasSemana = $concluidos * 1.5;

        // Atendimento em andamento
        $atendimentoEmAndamento = Atendimento::with(["cliente"])
            ->where("status_atual", "em_atendimento")
            ->where("funcionario_id", $funcionarioId)
            ->first();

        // Próximos atendimentos
        $proximosAtendimentos = collect();

        $periodo = $request->query("periodo", "todos");
        $periodoLabel = $periodo === "hoje" ? "Hoje" : ($periodo === "semana" ? "Esta Semana" : "Todos");

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
