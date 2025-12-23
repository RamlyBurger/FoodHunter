<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Security Log Service
 * 
 * OWASP [113-130]: Centralized security logging implementation
 * Logs security-relevant events for audit and monitoring
 */
class SecurityLogService
{
    private const CHANNEL = 'security';

    /**
     * OWASP [122]: Log authentication attempts
     */
    public static function logAuthAttempt(string $email, bool $success, ?string $ip = null, ?string $reason = null): void
    {
        $data = [
            'event' => 'auth_attempt',
            'email' => self::maskEmail($email),
            'success' => $success,
            'ip' => $ip ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        if (!$success && $reason) {
            $data['reason'] = $reason;
        }

        self::log($success ? 'info' : 'warning', 'Authentication attempt', $data);
    }

    /**
     * OWASP [123]: Log access control failures
     */
    public static function logAccessDenied(string $resource, ?int $userId = null, ?string $reason = null): void
    {
        self::log('warning', 'Access denied', [
            'event' => 'access_denied',
            'resource' => $resource,
            'user_id' => $userId,
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * OWASP [121]: Log input validation failures
     */
    public static function logValidationFailure(string $endpoint, array $errors, ?int $userId = null): void
    {
        self::log('notice', 'Validation failure', [
            'event' => 'validation_failure',
            'endpoint' => $endpoint,
            'errors' => array_keys($errors),
            'user_id' => $userId,
            'ip' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * OWASP [125]: Log invalid session token attempts
     */
    public static function logInvalidToken(?string $ip = null): void
    {
        self::log('warning', 'Invalid token attempt', [
            'event' => 'invalid_token',
            'ip' => $ip ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }


    /**
     * OWASP [124]: Log tampering attempts
     */
    public static function logTamperingAttempt(string $type, array $details = []): void
    {
        self::log('alert', 'Tampering attempt detected', [
            'event' => 'tampering_attempt',
            'type' => $type,
            'details' => $details,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log rate limit exceeded
     */
    public static function logRateLimitExceeded(string $identifier, string $endpoint): void
    {
        self::log('warning', 'Rate limit exceeded', [
            'event' => 'rate_limit_exceeded',
            'identifier' => self::maskEmail($identifier),
            'endpoint' => $endpoint,
            'ip' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * OWASP [66-67]: Log session revocation (single-device login enforcement)
     */
    public static function logSessionRevoked(int $userId, string $email, int $revokedTokens, string $reason, ?string $ip = null): void
    {
        self::log('info', 'Session tokens revoked', [
            'event' => 'session_revoked',
            'user_id' => $userId,
            'email' => self::maskEmail($email),
            'revoked_tokens' => $revokedTokens,
            'reason' => $reason,
            'ip_address' => $ip ?? request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log password change event
     */
    public static function logPasswordChange(string $email, ?string $ip = null): void
    {
        self::log('info', 'Password changed', [
            'event' => 'password_change',
            'email' => self::maskEmail($email),
            'ip_address' => $ip ?? request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log email change event
     */
    public static function logEmailChange(int $userId, string $newEmail, ?string $ip = null): void
    {
        self::log('info', 'Email changed', [
            'event' => 'email_change',
            'user_id' => $userId,
            'new_email' => self::maskEmail($newEmail),
            'ip_address' => $ip ?? request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * OWASP [119]: Mask sensitive data in logs
     */
    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }
        
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        
        return $maskedName . '@' . $domain;
    }

    /**
     * OWASP [118]: Master routine for logging
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        // Add request ID for tracing
        $context['request_id'] = request()->header('X-Request-ID') ?? uniqid('req_', true);
        
        try {
            Log::channel('daily')->$level("[SECURITY] {$message}", $context);
        } catch (\Exception $e) {
            // Fallback to default channel
            Log::$level("[SECURITY] {$message}", $context);
        }
    }
}
