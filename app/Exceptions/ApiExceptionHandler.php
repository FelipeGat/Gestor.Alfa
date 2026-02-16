<?php

namespace App\Exceptions;

use App\Domain\Exceptions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson() || $request->is('api/*') || $request->is('v1/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException($request, Throwable $e): JsonResponse
    {
        if ($e instanceof DomainException) {
            return $this->handleDomainException($e);
        }

        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($e);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException($e);
        }

        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return $this->handleAuthorizationException($e);
        }

        return $this->handleGenericException($e);
    }

    private function handleDomainException(DomainException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => $e->getErrors(),
            'code' => $e->getCode(),
        ], $e->getCode() ?: 422);
    }

    private function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $e->errors(),
        ], 422);
    }

    private function handleModelNotFoundException(ModelNotFoundException $e): JsonResponse
    {
        $model = class_basename($e->getModel());

        return response()->json([
            'success' => false,
            'message' => "{$model} não encontrado(a)",
        ], 404);
    }

    private function handleNotFoundHttpException(NotFoundHttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Rota não encontrada',
        ], 404);
    }

    private function handleAuthorizationException(Throwable $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized - Você não tem permissão para acessar este recurso',
        ], 403);
    }

    private function handleGenericException(Throwable $e): JsonResponse
    {
        $message = config('app.debug')
            ? $e->getMessage()
            : 'Erro interno do servidor';

        return response()->json([
            'success' => false,
            'message' => $message,
            'exception' => config('app.debug') ? get_class($e) : null,
        ], 500);
    }
}
