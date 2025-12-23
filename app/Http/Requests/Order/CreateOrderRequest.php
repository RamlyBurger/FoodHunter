<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:cash,card,ewallet'],
            'notes' => ['nullable', 'string', 'max:500'],
            'voucher_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
