<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use App\Models\AtendimentoAndamento;
use App\Models\AtendimentoPausa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AtendimentoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario_id;

        // Sempre filtra por funcionario_id do usuário logado
        $query = Atendimento::with(["cliente", "assunto"])
            ->where("funcionario_id", $funcionarioId);
        
        $query->when($request->status, fn($q) => $q->where("status_atual", $request->status))
              ->when($request->data, fn($q) => $q->whereDate("created_at", $request->data))
              ->orderByDesc("created_at");

        $atendimentos = $query->paginate(100);

        return response()->json($atendimentos);
    }

    public function show(int $id): JsonResponse
    {
        $atendimento = Atendimento::with(["cliente", "assunto", "andamentos", "pausas"])
            ->findOrFail($id);

        return response()->json($atendimento);
    }

    public function iniciar(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if ($atendimento->status_atual !== "aberto") {
            return response()->json(["message" => "Atendimento não pode ser iniciado"], 400);
        }

        $atendimento->update([
            "status_atual" => "em_atendimento",
            "iniciado_em" => now(),
            "iniciado_por_user_id" => auth()->id(),
        ]);

        AtendimentoAndamento::create([
            "atendimento_id" => $atendimento->id,
            "user_id" => auth()->id(),
            "status" => "iniciado",
            "descricao" => "Atendimento iniciado via app",
        ]);

        return response()->json($atendimento);
    }

    public function pausar(int $id, Request $request): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if ($atendimento->status_atual !== "em_atendimento") {
            return response()->json(["message" => "Atendimento não está em andamento"], 400);
        }

        $atendimento->update([
            "em_pausa" => true,
            "status_atual" => "pausado",
        ]);

        $pausa = AtendimentoPausa::create([
            "atendimento_id" => $atendimento->id,
            "inicio" => now(),
        ]);

        return response()->json($atendimento);
    }

    public function retomar(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if (!$atendimento->em_pausa) {
            return response()->json(["message" => "Atendimento não está pausado"], 400);
        }

        $atendimento->update([
            "em_execucao" => true,
            "em_pausa" => false,
            "status_atual" => "em_atendimento",
        ]);

        return response()->json($atendimento);
    }

    public function finalizar(int $id, Request $request): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if (!in_array($atendimento->status_atual, ["em_atendimento", "pausado"])) {
            return response()->json(["message" => "Atendimento não pode ser finalizado"], 400);
        }

        $atendimento->update([
            "status_atual" => "concluido",
            "finalizado_em" => now(),
            "finalizado_por_user_id" => auth()->id(),
            "observacoes_finais" => $request->observacoes,
        ]);

        return response()->json($atendimento);
    }

    /**
     * Retorna tempo de execução sincronizado com o servidor
     */
    public function tempo(int $id): JsonResponse
    {
        $user = request()->user();
        $query = Atendimento::with(['cliente', 'assunto']);

        // Admin vê qualquer atendimento, técnico vê apenas os seus
        if ($user->tipo !== 'admin' && $user->funcionario?->id) {
            $query->where('funcionario_id', $user->funcionario->id);
        }

        $atendimento = $query->findOrFail($id);

        // Calcula tempo total de execução
        $tempoExecucao = $atendimento->tempo_execucao_segundos ?? 0;

        // Se estiver em execução, calcula tempo desde o último início
        if ($atendimento->em_execucao && $atendimento->iniciado_em) {
            $tempoExecucao += now()->diffInSeconds($atendimento->iniciado_em);
        }

        return response()->json([
            'data' => [
                'tempo_execucao_segundos' => $tempoExecucao,
                'hora_inicio' => $atendimento->iniciado_em?->toIso8601String(),
                'hora_atual_servidor' => now()->toIso8601String(),
                'em_execucao' => $atendimento->em_execucao,
                'em_pausa' => $atendimento->em_pausa,
            ]
        ]);
    }
}
