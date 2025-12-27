<?php
/**
 * =============================================================================
 * RegisterRequest - Ng Wayne Xiang (User & Authentication Module)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang
 * @module     User & Authentication Module
 * @security   OWASP [38-39]: Password complexity and length requirements
 * 
 * Form request validation for user registration.
 * Enforces password complexity rules for security.
 * =============================================================================
 */

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // OWASP [11-14]: Validate data types, range, length, and whitelist
            'name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-\.]+$/u'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:100', 'unique:users,email'],
            // OWASP [38-39]: Password complexity - min 8 chars, mixed case, numbers, symbols
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and periods.',
            'phone.regex' => 'Phone number format is invalid.',
            'password.uncompromised' => 'This password has been compromised in a data breach. Please choose a different password.',
        ];
    }
}
