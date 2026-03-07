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

        return response()->json(['data' => $atendimento->fresh()]);
    }

    public function pausar(int $id, Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $atendimento = Atendimento::with(['cliente', 'assunto'])->findOrFail($id);

            // Validar permissão
            if ($user->tipo !== 'admin' && $user->funcionario?->id) {
                if ($atendimento->funcionario_id !== $user->funcionario->id) {
                    return response()->json(['message' => 'Não autorizado'], 403);
                }
            }

            // Validar se está em execução
            if (!$atendimento->em_execucao) {
                return response()->json(['message' => 'Atendimento não está em execução'], 400);
            }

            // Atualizar estado corretamente (NÃO muda status_atual, só em_execucao e em_pausa)
            $atendimento->update([
                "em_execucao" => false,
                "em_pausa" => true,
            ]);

            // Criar pausa com campos corretos
            $pausa = AtendimentoPausa::create([
                "atendimento_id" => $atendimento->id,
                "user_id" => auth()->id(),
                "tipo_pausa" => in_array($request->tipo_pausa, ['almoco', 'deslocamento', 'material', 'fim_dia']) ? $request->tipo_pausa : 'deslocamento',
                "iniciada_em" => now(),
            ]);

            return response()->json(['data' => $atendimento->fresh()]);

        } catch (\Exception $e) {
            // Log do erro para debug
            \Log::error('Erro ao pausar atendimento: ' . $e->getMessage(), [
                'atendimento_id' => $id,
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    public function retomar(int $id): JsonResponse
    {
        try {
            $atendimento = Atendimento::findOrFail($id);

            if (!$atendimento->em_pausa) {
                return response()->json(['message' => 'Atendimento não está pausado'], 400);
            }

            $atendimento->update([
                "em_execucao" => true,
                "em_pausa" => false,
                "status_atual" => "em_atendimento",
            ]);

            return response()->json(['data' => $atendimento->fresh()]);

        } catch (\Exception $e) {
            \Log::error('Erro ao retomar atendimento: ' . $e->getMessage(), [
                'atendimento_id' => $id,
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Server Error'], 500);
        }
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

        return response()->json(['data' => $atendimento->fresh()]);
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
