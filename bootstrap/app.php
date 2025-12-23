<?php

use App\Services\SecurityLogService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'vendor' => \App\Http\Middleware\EnsureUserIsVendor::class,
            'customer' => \App\Http\Middleware\EnsureUserIsCustomer::class,
        ]);

        // OWASP [94]: Rate limiting for API routes
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // OWASP [107-112]: Secure error handling - don't expose sensitive info
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = $e->getStatusCode();
                
                // OWASP [123]: Log access control failures
                if ($status === 403) {
                    SecurityLogService::logAccessDenied(
                        $request->path(),
                        $request->user()?->id,
                        'http_forbidden'
                    );
                }
                
                // OWASP [125]: Log invalid session attempts
                if ($status === 401) {
                    SecurityLogService::logInvalidToken($request->ip());
                }

                return response()->json([
                    'success' => false,
                    'status' => $status,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'error' => match($status) {
                        400 => 'BAD_REQUEST',
                        401 => 'UNAUTHORIZED',
                        403 => 'FORBIDDEN',
                        404 => 'NOT_FOUND',
                        405 => 'METHOD_NOT_ALLOWED',
                        422 => 'VALIDATION_ERROR',
                        429 => 'RATE_LIMIT_EXCEEDED',
                        500 => 'SERVER_ERROR',
                        default => 'ERROR',
                    },
                    'request_id' => uniqid('req_', true),
                    'timestamp' => now()->toIso8601String(),
                ], $status);
            }
        });

        // OWASP [121]: Log validation failures
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                SecurityLogService::logValidationFailure(
                    $request->path(),
                    $e->errors(),
                    $request->user()?->id
                );

                return response()->json([
                    'success' => false,
                    'status' => 422,
                    'message' => 'Validation failed',
                    'error' => 'VALIDATION_ERROR',
                    'errors' => $e->errors(),
                    'request_id' => uniqid('req_', true),
                    'timestamp' => now()->toIso8601String(),
                ], 422);
            }
        });

        // OWASP [126]: Log all system exceptions (but don't expose details)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Log the actual error internally
                \Log::error('Unhandled exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'request_path' => $request->path(),
                    'user_id' => $request->user()?->id,
                ]);

                // OWASP [108-109]: Return generic error message
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'An unexpected error occurred',
                    'error' => 'SERVER_ERROR',
                    'request_id' => uniqid('req_', true),
                    'timestamp' => now()->toIso8601String(),
                ], 500);
            }
        });
    })->create();
