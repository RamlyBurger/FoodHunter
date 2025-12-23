@php
    // Always show pagination, even for single page
    $alwaysShow = true;
@endphp
@if ($paginator->hasPages() || $alwaysShow)
@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $start = max(1, $currentPage - 1);
    $end = min($lastPage, $currentPage + 1);
@endphp
<style>
    .modern-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .modern-pagination .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border: none;
        border-radius: 10px;
        background: #fff;
        color: #333;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .modern-pagination .page-btn:hover:not(.disabled):not(.active) {
        background: #f0f0f0;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }
    .modern-pagination .page-btn.active {
        background: linear-gradient(135deg, #FF9500 0%, #FF6B00 100%);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 3px 10px rgba(255, 149, 0, 0.3);
    }
    .modern-pagination .page-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
    .modern-pagination .page-btn i {
        font-size: 12px;
    }
    .modern-pagination .page-dots {
        color: #999;
        font-size: 14px;
        padding: 0 4px;
    }
    .modern-pagination .page-input-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: 16px;
        padding-left: 16px;
        border-left: 1px solid #e0e0e0;
    }
    .modern-pagination .page-input {
        width: 50px;
        height: 40px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        text-align: center;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        transition: border-color 0.2s;
    }
    .modern-pagination .page-input:focus {
        border-color: #FF9500;
    }
    .modern-pagination .page-total {
        color: #666;
        font-size: 14px;
    }
    @media (max-width: 576px) {
        .modern-pagination .page-input-group {
            margin-left: 0;
            padding-left: 0;
            border-left: none;
            margin-top: 12px;
            width: 100%;
            justify-content: center;
        }
    }
</style>
<nav aria-label="Page navigation" style="margin-bottom: 60px;">
    <div class="modern-pagination">
        {{-- First Page --}}
        <a href="{{ $paginator->url(1) }}" class="page-btn {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <i class="bi bi-chevron-double-left"></i>
        </a>
        
        {{-- Previous Page --}}
        <a href="{{ $paginator->previousPageUrl() ?? '#' }}" class="page-btn {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <i class="bi bi-chevron-left"></i>
        </a>

        {{-- Page Numbers --}}
        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}" class="page-btn">1</a>
            @if ($start > 2)
                <span class="page-dots">...</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            <a href="{{ $paginator->url($page) }}" class="page-btn {{ $page == $currentPage ? 'active' : '' }}">{{ $page }}</a>
        @endfor

        @if ($end < $lastPage)
            @if ($end < $lastPage - 1)
                <span class="page-dots">...</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
        @endif

        {{-- Next Page --}}
        <a href="{{ $paginator->nextPageUrl() ?? '#' }}" class="page-btn {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
            <i class="bi bi-chevron-right"></i>
        </a>
        
        {{-- Last Page --}}
        <a href="{{ $paginator->url($lastPage) }}" class="page-btn {{ $currentPage == $lastPage ? 'disabled' : '' }}">
            <i class="bi bi-chevron-double-right"></i>
        </a>

        {{-- Page Input --}}
        <div class="page-input-group">
            <input type="number" 
                   class="page-input" 
                   min="1" 
                   max="{{ $lastPage }}" 
                   value="{{ $currentPage }}"
                   onkeydown="if(event.key==='Enter'){goToPage(this.value, {{ $lastPage }})}"
                   id="page-input">
            <span class="page-total">of {{ $lastPage }}</span>
        </div>
    </div>
</nav>
<script>
function goToPage(page, lastPage) {
    page = parseInt(page);
    if (page >= 1 && page <= lastPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    }
}
</script>
@endif
