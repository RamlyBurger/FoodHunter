<?php
/**
 * =============================================================================
 * LoginRequest - Ng Wayne Xiang (User & Authentication Module)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang
 * @module     User & Authentication Module
 * 
 * Form request validation for user login.
 * Validates email and password fields.
 * =============================================================================
 */

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
