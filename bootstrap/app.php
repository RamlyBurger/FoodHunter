<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load test routes
            if (file_exists(__DIR__.'/../routes/test-patterns.php')) {
                require __DIR__.'/../routes/test-patterns.php';
            }
            if (file_exists(__DIR__.'/../routes/test-security.php')) {
                require __DIR__.'/../routes/test-security.php';
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Redirect unauthenticated users appropriately
        $middleware->redirectGuestsTo(function (Request $request) {
            // For API routes, return null to trigger JSON response
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for authentication errors on API routes
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login to access this resource.',
                    'error' => 'unauthenticated'
                ], 401);
            }
        });

        // Return JSON for 404 errors on API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'error' => 'not_found'
                ], 404);
            }
        });
    })->create();
