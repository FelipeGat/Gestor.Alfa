<?php

namespace App\Http\Controllers\Api;

use App\Actions\BaixarCobrancaAction;
use App\Actions\CriarCobrancaAction;
use App\Actions\DTO\BaixarCobrancaDTO;
use App\Actions\DTO\CriarCobrancaDTO;
use App\Domain\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BaixarCobrancaRequest;
use App\Http\Requests\CriarCobrancaRequest;
use App\Models\Cobranca;
use App\Resources\CobrancaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CobrancaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Cobranca::with(['orcamento', 'orcamento.cliente']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('orcamento_id')) {
            $query->where('orcamento_id', $request->orcamento_id);
        }

        if ($request->filled('data_vencimento_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->data_vencimento_inicio);
        }

        if ($request->filled('data_vencimento_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->data_vencimento_fim);
        }

        $cobrancas = $query->orderBy('data_vencimento')->paginate(15);

        return response()->json([
            'data' => CobrancaResource::collection($cobrancas),
            'meta' => [
                'current_page' => $cobrancas->currentPage(),
                'last_page' => $cobrancas->lastPage(),
                'per_page' => $cobrancas->perPage(),
                'total' => $cobrancas->total(),
            ],
        ]);
    }

    public function store(CriarCobrancaRequest $request, CriarCobrancaAction $action): JsonResponse
    {
        try {
            $dto = CriarCobrancaDTO::fromArray([
                ...$request->validated(),
                'usuario_id' => Auth::id(),
            ]);

            $cobranca = $action->execute($dto);

            return response()->json([
                'message' => 'Cobrança criada com sucesso',
                'data' => new CobrancaResource($cobranca->load(['orcamento'])),
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $cobranca = Cobranca::with(['orcamento', 'orcamento.cliente', 'orcamento.empresa'])->find($id);

        if (! $cobranca) {
            return response()->json(['message' => 'Cobrança não encontrada'], 404);
        }

        return response()->json([
            'data' => new CobrancaResource($cobranca),
        ]);
    }

    public function baixa(BaixarCobrancaRequest $request, BaixarCobrancaAction $action, int $id): JsonResponse
    {
        try {
            $dto = BaixarCobrancaDTO::fromArray([
                ...$request->validated(),
                'cobranca_id' => $id,
                'usuario_id' => Auth::id(),
            ]);

            $cobranca = $action->execute($dto);

            return response()->json([
                'message' => 'Cobrança baixada com sucesso',
                'data' => new CobrancaResource($cobranca),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function pendentes(): JsonResponse
    {
        $cobrancas = Cobranca::with(['orcamento', 'orcamento.cliente'])
            ->where('status', 'pendente')
            ->orderBy('data_vencimento')
            ->paginate(15);

        return response()->json([
            'data' => CobrancaResource::collection($cobrancas),
            'meta' => [
                'current_page' => $cobrancas->currentPage(),
                'last_page' => $cobrancas->lastPage(),
                'per_page' => $cobrancas->perPage(),
                'total' => $cobrancas->total(),
            ],
        ]);
    }

    public function vencidas(): JsonResponse
    {
        $cobrancas = Cobranca::with(['orcamento', 'orcamento.cliente'])
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->orderBy('data_vencimento')
            ->paginate(15);

        return response()->json([
            'data' => CobrancaResource::collection($cobrancas),
            'meta' => [
                'current_page' => $cobrancas->currentPage(),
                'last_page' => $cobrancas->lastPage(),
                'per_page' => $cobrancas->perPage(),
                'total' => $cobrancas->total(),
            ],
        ]);
    }
}
