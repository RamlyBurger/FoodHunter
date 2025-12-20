<?php

namespace App\Services;

/**
 * Output Encoding Service
 * 
 * [19] Contextually output encode all data returned to the client that originated 
 * outside the application's trust boundary.
 * 
 * This service provides secure encoding methods for different output contexts:
 * - HTML context encoding
 * - JavaScript context encoding
 * - URL context encoding
 * - CSS context encoding
 */
class OutputEncodingService
{
    /**
     * Encode data for safe output in HTML context
     * 
     * @param mixed $data
     * @return string
     */
    public function encodeForHtml($data): string
    {
        if (is_null($data)) {
            return '';
        }
        
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Encode data for safe output in HTML attribute context
     * 
     * @param mixed $data
     * @return string
     */
    public function encodeForHtmlAttribute($data): string
    {
        if (is_null($data)) {
            return '';
        }
        
        // More aggressive encoding for attributes
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Encode data for safe output in JavaScript context
     * 
     * @param mixed $data
     * @return string
     */
    public function encodeForJavaScript($data): string
    {
        if (is_null($data)) {
            return '';
        }
        
        // Encode for JavaScript context - escape special characters
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Encode data for safe output in URL context
     * 
     * @param mixed $data
     * @return string
     */
    public function encodeForUrl($data): string
    {
        if (is_null($data)) {
            return '';
        }
        
        return urlencode((string)$data);
    }

    /**
     * Encode data for safe output in CSS context
     * 
     * @param mixed $data
     * @return string
     */
    public function encodeForCss($data): string
    {
        if (is_null($data)) {
            return '';
        }
        
        // Remove any potentially dangerous characters
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)$data);
    }

    /**
     * Sanitize payment data for display
     * Masks sensitive information like card numbers
     * 
     * @param array $paymentData
     * @return array
     */
    public function sanitizePaymentData(array $paymentData): array
    {
        $sanitized = [];
        
        foreach ($paymentData as $key => $value) {
            // Mask sensitive fields
            if (in_array($key, ['card_number', 'cvv', 'account_number'])) {
                $sanitized[$key] = $this->maskSensitiveData($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->encodeForHtml($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize order data for display
     * 
     * @param array $orderData
     * @return array
     */
    public function sanitizeOrderData(array $orderData): array
    {
        $sanitized = [];
        
        foreach ($orderData as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->encodeForHtml($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeOrderData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Mask sensitive data (show only last 4 characters)
     * 
     * @param string $data
     * @return string
     */
    private function maskSensitiveData(?string $data): string
    {
        if (is_null($data) || strlen($data) < 4) {
            return '****';
        }
        
        return str_repeat('*', strlen($data) - 4) . substr($data, -4);
    }

    /**
     * Encode entire response array for safe output
     * 
     * @param array $response
     * @return array
     */
    public function encodeResponse(array $response): array
    {
        $encoded = [];
        
        foreach ($response as $key => $value) {
            if (is_string($value)) {
                $encoded[$key] = $this->encodeForHtml($value);
            } elseif (is_array($value)) {
                $encoded[$key] = $this->encodeResponse($value);
            } else {
                $encoded[$key] = $value;
            }
        }
        
        return $encoded;
    }

    /**
     * Sanitize and encode monetary values
     * 
     * @param mixed $amount
     * @return string
     */
    public function encodeMonetaryValue($amount): string
    {
        // Ensure it's a valid number
        $sanitized = filter_var($amount, FILTER_VALIDATE_FLOAT);
        
        if ($sanitized === false) {
            return '0.00';
        }
        
        return number_format((float)$sanitized, 2, '.', '');
    }

    /**
     * Sanitize order ID for display
     * 
     * @param mixed $orderId
     * @return string
     */
    public function encodeOrderId($orderId): string
    {
        // Allow only alphanumeric and dashes
        return preg_replace('/[^a-zA-Z0-9\-]/', '', (string)$orderId);
    }
}
