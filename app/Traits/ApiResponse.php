<?php
/**
 * =============================================================================
 * ApiResponse Trait - Shared (All Students)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
 * @module     Shared Infrastructure
 * 
 * Provides standardized JSON API responses across all controllers.
 * Ensures consistent response format with success/error handling.
 * =============================================================================
 */

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

trait ApiResponse
{
    /**
     * Generate a unique request ID
     */
    protected function generateRequestId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'status' => $status,
            'message' => $message,
            'request_id' => $this->generateRequestId(),
            'timestamp' => now()->toIso8601String(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'Error',
        int $status = 400,
        ?string $error = null,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'status' => $status,
            'message' => $message,
            'error' => $error ?? $this->getErrorCodeFromStatus($status),
            'request_id' => $this->generateRequestId(),
            'timestamp' => now()->toIso8601String(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Created response (201)
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * No content response (204)
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Unauthorized response (401)
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Forbidden response (403)
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403, 'FORBIDDEN');
    }

    /**
     * Not found response (404)
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404, 'NOT_FOUND');
    }

    /**
     * Validation error response (422)
     */
    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, 'VALIDATION_ERROR', $errors);
    }

    /**
     * Too many requests response (429)
     */
    protected function tooManyRequestsResponse(string $message = 'Too many requests'): JsonResponse
    {
        return $this->errorResponse($message, 429, 'RATE_LIMIT_EXCEEDED');
    }

    /**
     * Server error response (500)
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, 500, 'SERVER_ERROR');
    }

    /**
     * Get error code from HTTP status
     */
    private function getErrorCodeFromStatus(int $status): string
    {
        return match ($status) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            429 => 'RATE_LIMIT_EXCEEDED',
            500 => 'SERVER_ERROR',
            502 => 'BAD_GATEWAY',
            503 => 'SERVICE_UNAVAILABLE',
            default => 'ERROR',
        };
    }
}
