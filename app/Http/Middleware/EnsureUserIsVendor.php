<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP [77-100]: Access Control middleware
 */
class EnsureUserIsVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        // OWASP [79]: Access controls should fail securely
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'message' => 'Unauthenticated.',
                    'error' => 'UNAUTHORIZED',
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!$request->user()->isVendor()) {
            // OWASP [123]: Log access control failures
            SecurityLogService::logAccessDenied(
                $request->path(),
                $request->user()->id,
                'non_vendor_access_attempt'
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'Access denied. Vendor account required.',
                    'error' => 'FORBIDDEN',
                    'request_id' => uniqid('req_', true),
                    'timestamp' => now()->toIso8601String(),
                ], 403);
            }
            
            return redirect()->route('home')
                ->with('error', 'Access denied. This area is for vendors only.');
        }

        if (!$request->user()->vendor) {
            // OWASP [123]: Log access control failures
            SecurityLogService::logAccessDenied(
                $request->path(),
                $request->user()->id,
                'vendor_profile_not_configured'
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'Vendor profile not configured.',
                    'error' => 'FORBIDDEN',
                    'request_id' => uniqid('req_', true),
                    'timestamp' => now()->toIso8601String(),
                ], 403);
            }
            
            return redirect()->route('home')
                ->with('error', 'Vendor profile not configured. Please contact support.');
        }

        return $next($request);
    }
}
