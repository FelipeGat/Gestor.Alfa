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
        $funcionarioId = $user->funcionario?->id;

        $pendentes = Atendimento::where('status_atual', 'pendente')
            ->where('funcionario_id', $funcionarioId)
            ->count();

        $emAndamento = Atendimento::where('status_atual', 'em_andamento')
            ->where('funcionario_id', $funcionarioId)
            ->count();

        $concluidosHoje = Atendimento::where('status_atual', 'finalizado')
            ->where('funcionario_id', $funcionarioId)
            ->whereDate('finalizado_em', Carbon::today())
            ->count();

        $proximosAtendimentos = Atendimento::with(['cliente', 'assunto'])
            ->where('status_atual', 'pendente')
            ->where('funcionario_id', $funcionarioId)
            ->whereNotNull('data_agendamento')
            ->orderBy('data_agendamento')
            ->limit(5)
            ->get();

        return response()->json([
            'pendentes' => $pendentes,
            'em_andamento' => $emAndamento,
            'concluidos_hoje' => $concluidosHoje,
            'proximos_atendimentos' => $proximosAtendimentos,
        ]);
    }
}
