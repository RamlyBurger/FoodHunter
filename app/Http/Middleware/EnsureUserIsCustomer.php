<?php
/**
 * =============================================================================
 * EnsureUserIsCustomer Middleware - Ng Wayne Xiang (User & Authentication Module)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang
 * @module     User & Authentication Module
 * @security   OWASP [77-100]: Access Control
 * 
 * Ensures user is a customer (not a vendor) for customer-only pages.
 * Prevents vendors from accessing customer routes like cart and orders.
 * =============================================================================
 */

namespace App\Http\Middleware;

use App\Services\SecurityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        // OWASP [79]: Access controls should fail securely
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Vendors should not access customer-only features
        if ($request->user()->isVendor()) {
            // OWASP [123]: Log access control failures
            SecurityLogService::logAccessDenied(
                $request->path(),
                $request->user()->id,
                'vendor_accessing_customer_page'
            );
            
            // For AJAX requests, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'This feature is for customers only. Please use the vendor dashboard.',
                    'error' => 'FORBIDDEN',
                    'request_id' => uniqid('req_', true),
                    'timestamp' => now()->toIso8601String(),
                ], 403);
            }
            
            // For web requests, redirect to vendor dashboard with message
            return redirect()->route('vendor.dashboard')
                ->with('error', 'This feature is for customers only. Please use the vendor dashboard.');
        }

        return $next($request);
    }
}
