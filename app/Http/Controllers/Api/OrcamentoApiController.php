<?php

namespace App\Http\Controllers\Api;

use App\Actions\CriarOrcamentoAction;
use App\Actions\DTO\CriarOrcamentoDTO;
use App\Domain\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CriarOrcamentoRequest;
use App\Models\Orcamento;
use App\Resources\CobrancaResource;
use App\Resources\OrcamentoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrcamentoApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Orcamento::with(['empresa', 'cliente', 'preCliente', 'itens']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', $search)
                    ->orWhere('descricao', 'like', $search);
            });
        }

        $orcamentos = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'data' => OrcamentoResource::collection($orcamentos),
            'meta' => [
                'current_page' => $orcamentos->currentPage(),
                'last_page' => $orcamentos->lastPage(),
                'per_page' => $orcamentos->perPage(),
                'total' => $orcamentos->total(),
            ],
        ]);
    }

    public function store(CriarOrcamentoRequest $request, CriarOrcamentoAction $action): JsonResponse
    {
        try {
            $dto = CriarOrcamentoDTO::fromArray([
                ...$request->validated(),
                'usuario_id' => Auth::id(),
            ]);

            $orcamento = $action->execute($dto);

            return response()->json([
                'message' => 'Orçamento criado com sucesso',
                'data' => new OrcamentoResource($orcamento->load(['empresa', 'cliente', 'itens'])),
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
        $orcamento = Orcamento::with(['empresa', 'cliente', 'preCliente', 'itens', 'centroCusto'])->find($id);

        if (! $orcamento) {
            return response()->json(['message' => 'Orçamento não encontrado'], 404);
        }

        return response()->json([
            'data' => new OrcamentoResource($orcamento),
        ]);
    }

    public function Cobrancas(int $orcamentoId): JsonResponse
    {
        $orcamento = Orcamento::find($orcamentoId);

        if (! $orcamento) {
            return response()->json(['message' => 'Orçamento não encontrado'], 404);
        }

        $cobrancas = $orcamento->cobrancas()->orderByDesc('data_vencimento')->get();

        return response()->json([
            'data' => CobrancaResource::collection($cobrancas),
        ]);
    }
}
