@props([
    'value' => 1,
    'min' => 1,
    'max' => 99,
    'itemId' => null,
    'cartItemId' => null,
    'size' => 'md'
])

@php
    $sizeClasses = [
        'sm' => ['wrapper' => 'stepper-sm', 'btn' => 'stepper-btn-sm', 'input' => 'stepper-input-sm'],
        'md' => ['wrapper' => '', 'btn' => '', 'input' => ''],
        'lg' => ['wrapper' => 'stepper-lg', 'btn' => 'stepper-btn-lg', 'input' => 'stepper-input-lg'],
    ];
    $classes = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="quantity-stepper {{ $classes['wrapper'] }}" data-item-id="{{ $itemId }}" data-cart-item-id="{{ $cartItemId }}">
    <button type="button" class="stepper-btn stepper-minus {{ $classes['btn'] }}" onclick="stepperMinus(this)">
        <i class="bi bi-dash"></i>
    </button>
    <input type="number" 
           class="stepper-input {{ $classes['input'] }}" 
           value="{{ $value }}" 
           min="{{ $min }}" 
           max="{{ $max }}"
           readonly>
    <button type="button" class="stepper-btn stepper-plus {{ $classes['btn'] }}" onclick="stepperPlus(this)">
        <i class="bi bi-plus"></i>
    </button>
</div>
