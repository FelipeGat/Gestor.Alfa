<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        $inicio = $request->get('inicio', Carbon::now()->startOfMonth());
        $fim = $request->get('fim', Carbon::now()->endOfMonth());

        $atendimentos = Atendimento::with(['cliente', 'assunto'])
            ->where('funcionario_id', $funcionarioId)
            ->whereNotNull('data_agendamento')
            ->whereBetween('data_agendamento', [$inicio, $fim])
            ->orderBy('data_agendamento')
            ->get();

        return response()->json($atendimentos);
    }

    public function hoje(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        $atendimentos = Atendimento::with(['cliente', 'assunto'])
            ->where('funcionario_id', $funcionarioId)
            ->whereDate('data_agendamento', Carbon::today())
            ->orderBy('data_agendamento')
            ->get();

        return response()->json($atendimentos);
    }

    public function disponibilidade(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        $data = $request->get('data', Carbon::today()->format('Y-m-d'));

        $atendimentos = Atendimento::where('funcionario_id', $funcionarioId)
            ->whereDate('data_agendamento', $data)
            ->orderBy('data_agendamento')
            ->get(['id', 'data_agendamento', 'periodo_agendamento', 'duracao_agendamento_minutos', 'status_atual']);

        return response()->json([
            'data' => $data,
            'atendimentos' => $atendimentos,
        ]);
    }
}
