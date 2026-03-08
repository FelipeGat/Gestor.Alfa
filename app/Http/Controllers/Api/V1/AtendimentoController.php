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

        $atendimentos->getCollection()->transform(function ($atendimento) {
            $endereco = '';
            $latitude = null;
            $longitude = null;
            
            if ($atendimento->cliente) {
                $endereco = implode(', ', array_filter([
                    $atendimento->cliente->logradouro,
                    $atendimento->cliente->numero,
                    $atendimento->cliente->bairro,
                    $atendimento->cliente->cidade,
                    $atendimento->cliente->estado,
                ]));
                $latitude = $atendimento->cliente->latitude;
                $longitude = $atendimento->cliente->longitude;
            }
            
            $atendimentoData = $atendimento->toArray();
            $atendimentoData['endereco'] = $endereco;
            $atendimentoData['cliente']['latitude'] = $latitude;
            $atendimentoData['cliente']['longitude'] = $longitude;
            
            return $atendimentoData;
        });

        return response()->json($atendimentos);
    }

    public function show(int $id): JsonResponse
    {
        $atendimento = Atendimento::with(["cliente", "assunto", "andamentos", "pausas"])
            ->findOrFail($id);

        $endereco = '';
        $latitude = null;
        $longitude = null;

        if ($atendimento->cliente) {
            $endereco = implode(', ', array_filter([
                $atendimento->cliente->logradouro,
                $atendimento->cliente->numero,
                $atendimento->cliente->bairro,
                $atendimento->cliente->cidade,
                $atendimento->cliente->estado,
            ]));
            $latitude = $atendimento->cliente->latitude;
            $longitude = $atendimento->cliente->longitude;
        }

        $atendimentoData = $atendimento->toArray();
        $atendimentoData['endereco'] = $endereco;
        $atendimentoData['cliente']['latitude'] = $latitude;
        $atendimentoData['cliente']['longitude'] = $longitude;

        return response()->json($atendimentoData);
    }

    public function iniciar(int $id): JsonResponse
    {
        $atendimento = Atendimento::findOrFail($id);

        // Se já estiver em atendimento mas com flags erradas (limbo), permite "reiniciar" ou corrigir
        if ($atendimento->status_atual === "em_atendimento" && !$atendimento->em_execucao && !$atendimento->em_pausa) {
             $atendimento->update([
                "em_execucao" => true,
                "em_pausa" => false,
                "iniciado_em" => now(),
            ]);
            return response()->json(['data' => $atendimento->fresh(), 'message' => 'Estado corrigido e atendimento iniciado']);
        }

        if ($atendimento->status_atual !== "aberto") {
            return response()->json(["message" => "Atendimento não pode ser iniciado. Status atual: " . $atendimento->status_atual], 400);
        }

        $atendimento->update([
            "status_atual" => "em_atendimento",
            "iniciado_em" => now(),
            "iniciado_por_user_id" => auth()->id(),
            "em_execucao" => true,
            "em_pausa" => false,
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

            $agora = now();
            $tempoDecorrido = 0;

            if ($atendimento->iniciado_em) {
                $tempoDecorrido = max(0, $agora->timestamp - $atendimento->iniciado_em->timestamp);
            }

            $novoTempoExecucao = ($atendimento->tempo_execucao_segundos ?? 0) + $tempoDecorrido;

            // Atualizar estado corretamente
            $atendimento->update([
                "em_execucao" => false,
                "em_pausa" => true,
                "tempo_execucao_segundos" => $novoTempoExecucao,
            ]);

            // Criar pausa com campos corretos
            AtendimentoPausa::create([
                "atendimento_id" => $atendimento->id,
                "user_id" => auth()->id(),
                "tipo_pausa" => in_array($request->tipo_pausa, ['almoco', 'deslocamento', 'material', 'fim_dia']) ? $request->tipo_pausa : 'deslocamento',
                "iniciada_em" => $agora,
            ]);

            return response()->json(['data' => $atendimento->fresh()]);

        } catch (\Exception $e) {
            \Log::error('Erro ao pausar atendimento: ' . $e->getMessage(), [
                'atendimento_id' => $id,
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function retomar(int $id): JsonResponse
    {
        try {
            $atendimento = Atendimento::findOrFail($id);

            // Se estiver no limbo (em_atendimento mas flags false), forçar retomada
            if ($atendimento->status_atual === "em_atendimento" && !$atendimento->em_execucao && !$atendimento->em_pausa) {
                $atendimento->update([
                    "em_execucao" => true,
                    "em_pausa" => false,
                    "iniciado_em" => now(),
                ]);
                return response()->json(['data' => $atendimento->fresh(), 'message' => 'Atendimento retirado do estado inconsistente']);
            }

            if (!$atendimento->em_pausa) {
                return response()->json(['message' => 'Atendimento não está pausado'], 400);
            }

            $pausaAtiva = $atendimento->pausaAtiva();
            $tempoPausa = 0;
            if ($pausaAtiva) {
                $pausaAtiva->encerrar();
                $tempoPausa = $pausaAtiva->tempo_segundos ?? 0;
            }

            $novoTempoPausa = ($atendimento->tempo_pausa_segundos ?? 0) + $tempoPausa;

            $atendimento->update([
                "em_execucao" => true,
                "em_pausa" => false,
                "status_atual" => "em_atendimento",
                "iniciado_em" => now(), // Reinicia base para próximo cálculo de execução
                "tempo_pausa_segundos" => $novoTempoPausa,
            ]);

            return response()->json(['data' => $atendimento->fresh()]);

        } catch (\Exception $e) {
            \Log::error('Erro ao retomar atendimento: ' . $e->getMessage(), [
                'atendimento_id' => $id,
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function finalizar(int $id, Request $request): JsonResponse
    {
        try {
            $atendimento = Atendimento::findOrFail($id);

            if (!in_array($atendimento->status_atual, ["em_atendimento", "pausado"])) {
                return response()->json(["message" => "Atendimento não pode ser finalizado. Status: " . $atendimento->status_atual], 400);
            }

            // Validação rigorosa (Mobile deve enviar multipart/form-data)
            $request->validate([
                'fotos' => 'required|array|min:1',
                'fotos.*' => 'required|image|max:5120',
                'observacoes' => 'required|string|min:5',
                'assinatura_cliente_nome' => 'required|string|min:2',
                'assinatura_cliente_cargo' => 'required|string|min:2',
                'assinatura_cliente' => 'required|string', // Base64 da imagem
            ]);

            $agora = now();
            $tempoExecucao = $atendimento->tempo_execucao_segundos ?? 0;

            // 1. Cálculo de tempo final se ainda estiver em execução
            if ($atendimento->em_execucao && $atendimento->iniciado_em) {
                $tempoDecorrido = max(0, $agora->timestamp - $atendimento->iniciado_em->timestamp);
                $tempoExecucao += $tempoDecorrido;
            }

            // 2. Encerrar pausas ativas se houver
            if ($atendimento->em_pausa) {
                $pausaAtiva = $atendimento->pausaAtiva();
                if ($pausaAtiva) {
                    $pausaAtiva->encerrar();
                    $atendimento->tempo_pausa_segundos = ($atendimento->tempo_pausa_segundos ?? 0) + ($pausaAtiva->tempo_segundos ?? 0);
                }
            }

            // 3. Processar Assinatura (Base64 -> Arquivo)
            $assinaturaPath = null;
            if (preg_match('/^data:image\/(\w+);base64,/', $request->assinatura_cliente, $matches)) {
                $extensao = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                $conteudo = base64_decode(substr($request->assinatura_cliente, strpos($request->assinatura_cliente, ',') + 1));
                
                $nomeArquivo = 'assinatura_' . uniqid() . '.' . $extensao;
                $assinaturaPath = 'atendimentos/assinaturas/' . $nomeArquivo;
                \Storage::disk('public')->put($assinaturaPath, $conteudo);
            }

            // 4. Atualizar Atendimento (Status 'finalizacao' aguarda aprovação do gerente)
            $atendimento->update([
                "status_atual" => "finalizacao",
                "finalizado_em" => $agora,
                "finalizado_por_user_id" => auth()->id(),
                "observacoes_finais" => $request->observacoes,
                "em_execucao" => false,
                "em_pausa" => false,
                "tempo_execucao_segundos" => $tempoExecucao,
                "assinatura_cliente_nome" => trim($request->assinatura_cliente_nome),
                "assinatura_cliente_cargo" => trim($request->assinatura_cliente_cargo),
                "assinatura_cliente_path" => $assinaturaPath,
            ]);

            // 5. Criar Andamento Final e Salvar Fotos
            $andamento = AtendimentoAndamento::create([
                "atendimento_id" => $atendimento->id,
                "user_id" => auth()->id(),
                "status" => "finalizado",
                "descricao" => $request->observacoes,
            ]);

            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    $path = $foto->store('atendimentos/fotos', 'public');
                    // Salva caminho relativo sem o prefixo public/
                    $relativePath = ltrim(str_replace(['public/', 'storage/'], '', $path), '/');
                    $andamento->fotos()->create([
                        'arquivo' => $relativePath,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Atendimento finalizado com sucesso. Aguardando aprovação.',
                'data' => $atendimento->fresh(['cliente', 'assunto'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Dados inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao finalizar atendimento via API: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao finalizar: ' . $e->getMessage()], 500);
        }
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

        // Retorna apenas tempo acumulado salvo no banco
        // NÃO calcular desde iniciado_em para evitar valores negativos/inflados
        $tempoExecucao = $atendimento->tempo_execucao_segundos ?? 0;

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
