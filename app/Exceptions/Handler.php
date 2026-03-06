<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
    }

    protected function shouldReturnJson($request, Throwable $e)
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    protected function convertExceptionToArray(Throwable $e): array
    {
        $statusCode = 500;
        if ($this->isHttpException($e) && method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        }

        return [
            'success' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : $this->getProductionMessage($statusCode),
        ];
    }

    private function getProductionMessage(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'Server error. Please try again later.',
            $statusCode === 404 => 'Resource not found.',
            $statusCode === 403 => 'Forbidden.',
            $statusCode === 401 => 'Unauthenticated.',
            $statusCode === 422 => 'Validation failed.',
            default => 'An error occurred.',
        };
    }
}
