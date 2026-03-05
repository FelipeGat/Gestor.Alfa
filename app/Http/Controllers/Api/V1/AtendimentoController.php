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
        $funcionarioId = $user->funcionario?->id;

        $atendimentos = Atendimento::with(['cliente', 'assunto'])
            ->where('funcionario_id', $funcionarioId)
            ->when($request->status, fn($q) => $q->where('status_atual', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($atendimentos);
    }

    public function show(int $id): JsonResponse
    {
        $atendimento = Atendimento::with(['cliente', 'assunto', 'andamentos', 'pausas'])
            ->findOrFail($id);

        return response()->json($atendimento);
    }

    public function iniciar(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if ($atendimento->status_atual !== 'pendente') {
            return response()->json(['message' => 'Atendimento não pode ser iniciado'], 400);
        }

        $atendimento->update([
            'status_atual' => 'em_andamento',
            'iniciado_em' => now(),
            'iniciado_por_user_id' => auth()->id(),
        ]);

        AtendimentoAndamento::create([
            'atendimento_id' => $atendimento->id,
            'user_id' => auth()->id(),
            'status' => 'iniciado',
            'descricao' => 'Atendimento iniciado via app',
        ]);

        return response()->json($atendimento);
    }

    public function pausar(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if ($atendimento->status_atual !== 'em_andamento') {
            return response()->json(['message' => 'Atendimento não está em andamento'], 400);
        }

        $atendimento->update([
            'em_pausa' => true,
            'status_atual' => 'pausado',
        ]);

        $pausa = AtendimentoPausa::create([
            'atendimento_id' => $atendimento->id,
            'inicio' => now(),
        ]);

        return response()->json($atendimento);
    }

    public function retomar(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if ($atendimento->status_atual !== 'pausado') {
            return response()->json(['message' => 'Atendimento não está pausado'], 400);
        }

        $pausa = $atendimento->pausas()->whereNull('fim')->first();
        if ($pausa) {
            $pausa->update(['fim' => now()]);
            
            $tempoPausa = $pausa->inicio->diffInSeconds($pausa->fim);
            $atendimento->update([
                'tempo_pausa_segundos' => ($atendimento->tempo_pausa_segundos ?? 0) + $tempoPausa,
            ]);
        }

        $atendimento->update([
            'em_pausa' => false,
            'status_atual' => 'em_andamento',
        ]);

        return response()->json($atendimento);
    }

    public function finalizar(Request $request, int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        if (!in_array($atendimento->status_atual, ['em_andamento', 'pausado'])) {
            return response()->json(['message' => 'Atendimento não pode ser finalizado'], 400);
        }

        $validated = $request->validate([
            'descricao' => 'required|string',
        ]);

        $tempoTotal = $atendimento->inicio->diffInSeconds(now());
        $tempoExecutado = $tempoTotal - ($atendimento->tempo_pausa_segundos ?? 0);

        $atendimento->update([
            'status_atual' => 'finalizado',
            'finalizado_em' => now(),
            'finalizado_por_user_id' => auth()->id(),
            'tempo_execucao_segundos' => $tempoExecutado,
        ]);

        AtendimentoAndamento::create([
            'atendimento_id' => $atendimento->id,
            'user_id' => auth()->id(),
            'status' => 'finalizado',
            'descricao' => $validated['descricao'],
        ]);

        return response()->json($atendimento);
    }

    public function atualizarTempo(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        $tempoTotal = $atendimento->inicio ? $atendimento->inicio->diffInSeconds(now()) : 0;
        $tempoExecutado = $tempoTotal - ($atendimento->tempo_pausa_segundos ?? 0);

        return response()->json([
            'tempo_total' => $tempoTotal,
            'tempo_executado' => $tempoExecutado,
            'em_pausa' => $atendimento->em_pausa,
        ]);
    }
}
