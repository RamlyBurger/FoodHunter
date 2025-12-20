<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request and enhance API responses
     * Adds timestamp, request_id, and status to all API responses
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique request ID
        $requestId = Str::uuid()->toString();
        $request->attributes->set('request_id', $requestId);
        
        $response = $next($request);
        
        // Only enhance JSON responses for API routes
        if ($request->is('api/*') && $response instanceof \Illuminate\Http\JsonResponse) {
            $originalData = $response->getData(true);
            
            // Add standard fields to response
            $enhancedData = array_merge([
                'timestamp' => now()->toIso8601String(),
                'request_id' => $requestId,
                'status' => $this->getStatusText($response->getStatusCode()),
            ], $originalData);
            
            $response->setData($enhancedData);
        }
        
        return $response;
    }
    
    /**
     * Get human-readable status text from HTTP status code
     */
    private function getStatusText(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 200 && $statusCode < 300 => 'success',
            $statusCode >= 400 && $statusCode < 500 => 'client_error',
            $statusCode >= 500 => 'server_error',
            default => 'unknown',
        };
    }
}
