<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
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

    public function render($request, Throwable $e): mixed
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated. Please login to continue.',
                'data'    => null,
            ], 401);
        }

        if ($e instanceof AccessDeniedHttpException) {
            return response()->json([
                'message' => $e->getMessage() ?: 'You do not have permission to perform this action.',
                'data'    => null,
            ], 403);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found.',
                'data'    => null,
            ], 404);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                'data'    => null,
            ], $e->getStatusCode());
        }

        // Generic server error — hide details in production
        return response()->json([
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'An unexpected error occurred. Please try again later.',
            'data'    => null,
        ], 500);
    }
}
