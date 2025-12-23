@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-heart-fill text-danger me-2"></i>
                My Wishlist
            </h2>
            <p class="text-muted">Your saved favorite items</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="{{ route('menu.index') }}" class="btn btn-outline-primary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Continue Browsing
            </a>
        </div>
    </div>

    @if($wishlistItems->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-heart display-1 text-muted"></i>
                <h4 class="mt-3">Your wishlist is empty</h4>
                <p class="text-muted">Save your favorite items here for later!</p>
                <a href="{{ route('menu.index') }}" class="btn btn-primary mt-2 rounded-pill">
                    <i class="bi bi-search me-1"></i> Browse Menu
                </a>
            </div>
        </div>
    @else
        @php
            $wishlistIds = $wishlistItems->pluck('menu_item_id')->toArray();
        @endphp
        <div class="row g-4">
            @foreach($wishlistItems as $wishlistItem)
                <x-menu-item-card :item="$wishlistItem->menuItem" :wishlistIds="$wishlistIds" />
            @endforeach
        </div>
    @endif
</div>
@endsection
