<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            $status = 500;
            $message = 'Erro interno do servidor';

            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                $status = 401;
                $message = 'Unauthorized';
            } elseif ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                $status = 403;
                $message = 'Forbidden';
            } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $status = 404;
                $message = 'Not Found';
            } elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
                $status = 422;
                $message = 'Validation Error';
                return response()->json([
                    'statusCode' => $status,
                    'message' => $message,
                    'errors' => $exception->errors(),
                    'timestamp' => now()->toIso8601String(),
                    'path' => $request->path(),
                ], $status);
            }

            return response()->json([
                'statusCode' => $status,
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
                'path' => $request->path(),
            ], $status);
        }

        return parent::render($request, $exception);
    }
}
