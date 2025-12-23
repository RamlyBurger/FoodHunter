<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailJSService
{
    protected string $publicKey;
    protected string $privateKey;
    protected string $serviceId;
    protected string $templateId;

    public function __construct()
    {
        $this->publicKey = config('services.emailjs.public_key');
        $this->privateKey = config('services.emailjs.private_key');
        $this->serviceId = config('services.emailjs.service_id');
        $this->templateId = config('services.emailjs.template_id');
    }

    /**
     * Send OTP email using EmailJS
     */
    public function sendOtp(string $email, string $code, string $type = 'signup'): array
    {
        try {
            $expiryTime = $type === 'signup' ? '15 minutes' : '15 minutes';
            
            $response = Http::post('https://api.emailjs.com/api/v1.0/email/send', [
                'service_id' => $this->serviceId,
                'template_id' => $this->templateId,
                'user_id' => $this->publicKey,
                'accessToken' => $this->privateKey,
                'template_params' => [
                    'email' => $email,
                    'passcode' => $code,
                    'time' => $expiryTime,
                ],
            ]);

            if ($response->successful() || $response->body() === 'OK') {
                Log::info('EmailJS: OTP sent successfully to ' . $email);
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                ];
            }

            Log::error('EmailJS error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Failed to send OTP: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('EmailJS exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to email service',
            ];
        }
    }

    /**
     * Send password reset email using EmailJS
     * OWASP [49]: Secure password reset link delivery
     */
    public function sendPasswordResetEmail($user, string $resetUrl): array
    {
        try {
            $response = Http::post('https://api.emailjs.com/api/v1.0/email/send', [
                'service_id' => $this->serviceId,
                'template_id' => config('services.emailjs.password_reset_template_id', $this->templateId),
                'user_id' => $this->publicKey,
                'accessToken' => $this->privateKey,
                'template_params' => [
                    'email' => $user->email,
                    'to_name' => $user->name,
                    'passcode' => 'Click the link below to reset your password:',
                    'time' => '1 hour',
                    'reset_link' => $resetUrl,
                ],
            ]);

            if ($response->successful() || $response->body() === 'OK') {
                Log::info('EmailJS: Password reset email sent to ' . $user->email);
                return [
                    'success' => true,
                    'message' => 'Password reset email sent successfully',
                ];
            }

            Log::error('EmailJS password reset error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Failed to send password reset email: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('EmailJS password reset exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to email service',
            ];
        }
    }
}
