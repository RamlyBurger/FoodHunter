<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * SecurityLoggingService - Centralized security event logging
 * 
 * Security Implementation:
 * [118] Utilize a master routine for all logging operations
 * [122] Log all authentication attempts, especially failures
 * [119] Do not store sensitive information in logs
 * [115] Ensure logs contain important log event data
 */
class SecurityLoggingService
{
    /**
     * Log authentication attempt
     * [122] Log all authentication attempts, especially failures
     * 
     * @param string $type Type of authentication (login, register, logout)
     * @param bool $success Whether authentication was successful
     * @param array $context Additional context (user_id, email, etc.)
     */
    public function logAuthenticationAttempt(string $type, bool $success, array $context = []): void
    {
        $logLevel = $success ? 'info' : 'warning';
        $message = $success ? 'Authentication successful' : 'Authentication failed';

        // [119] Do not store sensitive information in logs (no passwords)
        $sanitizedContext = array_merge([
            'auth_type' => $type,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ], $this->sanitizeLogData($context));

        Log::log($logLevel, $message, $sanitizedContext);
    }

    /**
     * Log access control failure
     * [123] Log all access control failures
     * 
     * @param string $resource Resource being accessed
     * @param string $action Action attempted
     * @param array $context Additional context
     */
    public function logAccessControlFailure(string $resource, string $action, array $context = []): void
    {
        Log::warning('Access control failure', array_merge([
            'resource' => $resource,
            'action' => $action,
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toDateTimeString(),
        ], $context));
    }

    /**
     * Log input validation failure
     * [121] Log all input validation failures
     * 
     * @param string $context Context of validation (form name, endpoint, etc.)
     * @param array $errors Validation errors
     */
    public function logValidationFailure(string $context, array $errors): void
    {
        Log::notice('Input validation failure', [
            'context' => $context,
            'errors' => $errors,
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log system exception
     * [126] Log all system exceptions
     * [107] Do not disclose sensitive information - sanitize before logging
     * 
     * @param \Exception $exception The exception
     * @param string $context Context where exception occurred
     * @param array $additionalData Additional data to log
     */
    public function logException(\Exception $exception, string $context, array $additionalData = []): void
    {
        Log::error('System exception occurred', array_merge([
            'context' => $context,
            'exception_type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ], $this->sanitizeLogData($additionalData)));
    }

    /**
     * Log administrative function
     * [127] Log all administrative functions
     * 
     * @param string $action Administrative action performed
     * @param array $context Context and changes made
     */
    public function logAdministrativeAction(string $action, array $context = []): void
    {
        Log::info('Administrative action', array_merge([
            'action' => $action,
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ], $context));
    }

    /**
     * Log apparent tampering event
     * [124] Log all apparent tampering events
     * 
     * @param string $type Type of tampering detected
     * @param array $context Context of the event
     */
    public function logTamperingAttempt(string $type, array $context = []): void
    {
        Log::alert('Tampering attempt detected', array_merge([
            'tampering_type' => $type,
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ], $context));
    }

    /**
     * Log file upload attempt
     * 
     * @param string $context Upload context
     * @param bool $success Whether upload was successful
     * @param array $details File details
     */
    public function logFileUpload(string $context, bool $success, array $details = []): void
    {
        $logLevel = $success ? 'info' : 'warning';
        $message = $success ? 'File upload successful' : 'File upload failed';

        Log::log($logLevel, $message, array_merge([
            'context' => $context,
            'success' => $success,
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ], $this->sanitizeLogData($details)));
    }

    /**
     * Sanitize log data to remove sensitive information
     * [119] Do not store sensitive information in logs
     * 
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    private function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'card_number',
            'cvv',
            'ssn',
        ];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);
            
            // Remove sensitive keys entirely
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (str_contains($lowerKey, $sensitiveKey)) {
                    $data[$key] = '[REDACTED]';
                    break;
                }
            }

            // Recursively sanitize nested arrays
            if (is_array($value)) {
                $data[$key] = $this->sanitizeLogData($value);
            }
        }

        return $data;
    }

    /**
     * Log security configuration change
     * [127] Log all administrative functions, including changes to security configuration
     * 
     * @param string $setting Setting that was changed
     * @param mixed $oldValue Old value (will be sanitized)
     * @param mixed $newValue New value (will be sanitized)
     */
    public function logSecurityConfigurationChange(string $setting, $oldValue, $newValue): void
    {
        Log::warning('Security configuration changed', [
            'setting' => $setting,
            'old_value' => $this->sanitizeLogData(['value' => $oldValue])['value'],
            'new_value' => $this->sanitizeLogData(['value' => $newValue])['value'],
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get sanitized request context for logging
     * 
     * @return array
     */
    public function getRequestContext(): array
    {
        return [
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
        ];
    }
}
