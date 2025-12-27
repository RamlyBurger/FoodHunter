<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Supabase Service - Ng Wayne Xiang
 * 
 * Handles email verification using Supabase Auth OTP.
 * Replaces EmailJS for more reliable email delivery.
 */
class SupabaseService
{
    private string $supabaseUrl;
    private string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->supabaseKey = config('services.supabase.anon_key');
    }

    /**
     * Send OTP to email using Supabase Auth
     */
    public function sendOtp(string $email): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/otp", [
                'email' => $email,
            ]);

            if ($response->successful()) {
                Log::info('Supabase OTP sent successfully', ['email' => $email]);
                return [
                    'success' => true,
                    'message' => 'Verification code sent to your email.',
                ];
            }

            $error = $response->json();
            Log::error('Supabase OTP failed', ['email' => $email, 'error' => $error]);
            
            return [
                'success' => false,
                'message' => $error['message'] ?? 'Failed to send verification code.',
            ];
        } catch (\Exception $e) {
            Log::error('Supabase OTP exception', ['email' => $email, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ];
        }
    }

    /**
     * Verify OTP using Supabase Auth
     * 
     * @param string $email The email address
     * @param string $token The 6-digit OTP token
     * @param string $type The OTP type: 'email' (signup), 'magiclink' (email change), 'recovery' (password reset)
     */
    public function verifyOtp(string $email, string $token, string $type = 'email'): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/verify", [
                'email' => $email,
                'token' => $token,
                'type' => $type,
            ]);

            if ($response->successful()) {
                Log::info('Supabase OTP verified successfully', ['email' => $email, 'type' => $type]);
                return [
                    'success' => true,
                    'message' => 'Email verified successfully.',
                    'data' => $response->json(),
                ];
            }

            $error = $response->json();
            Log::error('Supabase OTP verification failed', ['email' => $email, 'type' => $type, 'error' => $error]);
            
            return [
                'success' => false,
                'message' => $error['message'] ?? 'Invalid or expired verification code.',
            ];
        } catch (\Exception $e) {
            Log::error('Supabase OTP verification exception', ['email' => $email, 'type' => $type, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.',
            ];
        }
    }
}
