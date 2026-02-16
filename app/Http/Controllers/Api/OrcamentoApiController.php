<?php

namespace App\Http\Controllers\Api;

use App\Actions\AtualizarOrcamentoAction;
use App\Actions\CriarOrcamentoAction;
use App\Actions\DTO\AtualizarOrcamentoDTO;
use App\Actions\DTO\CriarOrcamentoDTO;
use App\Actions\ExcluirOrcamentoAction;
use App\Actions\ListarOrcamentosAction;
use App\Domain\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarOrcamentoRequest;
use App\Http\Requests\CriarOrcamentoRequest;
use App\Models\Orcamento;
use App\Resources\CobrancaResource;
use App\Resources\OrcamentoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrcamentoApiController extends Controller
{
    public function index(Request $request, ListarOrcamentosAction $action): JsonResponse
    {
        try {
            $orcamentos = $action->execute($request->all());

            return response()->json([
                'data' => OrcamentoResource::collection($orcamentos),
                'meta' => [
                    'current_page' => $orcamentos->currentPage(),
                    'last_page' => $orcamentos->lastPage(),
                    'per_page' => $orcamentos->perPage(),
                    'total' => $orcamentos->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar orçamentos',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
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

    public function update(AtualizarOrcamentoRequest $request, AtualizarOrcamentoAction $action, int $id): JsonResponse
    {
        try {
            $dto = AtualizarOrcamentoDTO::fromArray($request->validated());

            $orcamento = $action->execute($id, $dto, Auth::id());

            return response()->json([
                'message' => 'Orçamento atualizado com sucesso',
                'data' => new OrcamentoResource($orcamento->fresh()),
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

    public function destroy(int $id, ExcluirOrcamentoAction $action): JsonResponse
    {
        try {
            $action->execute($id, Auth::id());

            return response()->json([
                'message' => 'Orçamento excluído com sucesso',
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

    public function cobrancas(int $orcamentoId): JsonResponse
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
