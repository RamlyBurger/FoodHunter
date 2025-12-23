@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-bell me-2" style="color: var(--primary-color);"></i>Notifications</h2>
        @if($notifications->where('is_read', false)->count() > 0)
        <button type="button" class="btn btn-outline-primary btn-sm" id="mark-all-read-btn" onclick="markAllAsRead()">
            <i class="bi bi-check-all me-1"></i> Mark All as Read
        </button>
        @endif
    </div>

    @if($notifications->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            @foreach($notifications as $index => $notification)
            <div class="p-4 {{ $index > 0 ? 'border-top' : '' }} {{ $notification->is_read ? '' : 'bg-light' }}" style="{{ !$notification->is_read ? 'background: rgba(0, 122, 255, 0.03) !important;' : '' }}">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        @switch($notification->type)
                            @case('order_created')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(0, 122, 255, 0.15);">
                                    <i class="bi bi-bag-plus" style="color: var(--primary-color);"></i>
                                </div>
                                @break
                            @case('order_status')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(88, 86, 214, 0.15);">
                                    <i class="bi bi-arrow-repeat" style="color: var(--secondary-color);"></i>
                                </div>
                                @break
                            @case('order_completed')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(52, 199, 89, 0.15);">
                                    <i class="bi bi-bag-check" style="color: var(--success-color);"></i>
                                </div>
                                @break
                            @case('points_earned')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 149, 0, 0.15);">
                                    <i class="bi bi-star" style="color: var(--warning-color);"></i>
                                </div>
                                @break
                            @case('welcome')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(0, 122, 255, 0.15);">
                                    <i class="bi bi-emoji-smile" style="color: var(--primary-color);"></i>
                                </div>
                                @break
                            @default
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--gray-100);">
                                    <i class="bi bi-bell" style="color: var(--gray-500);"></i>
                                </div>
                        @endswitch
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong style="font-weight: 600;">{{ $notification->title }}</strong>
                            @if(!$notification->is_read)
                            <span class="badge bg-primary" style="font-size: 0.65rem;">New</span>
                            @endif
                        </div>
                        <p class="mb-1 text-muted" style="font-size: 0.9rem;">{{ $notification->message }}</p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        @if(!$notification->is_read)
                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Mark as read" style="padding: 4px 8px;" onclick="markAsRead({{ $notification->id }}, this)">
                            <i class="bi bi-check"></i>
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" style="padding: 4px 8px;" onclick="deleteNotification({{ $notification->id }}, this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @else
    <div class="text-center py-5">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: var(--gray-100);">
            <i class="bi bi-bell-slash" style="font-size: 48px; color: var(--gray-400);"></i>
        </div>
        <h4 class="mt-3">No notifications</h4>
        <p class="text-muted">You're all caught up!</p>
    </div>
    @endif
</div>
</div>

@push('scripts')
<script>
function markAsRead(notificationId, btn) {
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    fetch('/notifications/' + notificationId + '/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove the button and update UI
            const notificationRow = btn.closest('.p-4');
            notificationRow.classList.remove('bg-light');
            notificationRow.style.background = '';
            const badge = notificationRow.querySelector('.badge.bg-primary');
            if (badge) badge.remove();
            btn.remove();
            showToast('Marked as read', 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            showToast(data.message || 'Failed to mark as read', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        showToast('An error occurred', 'error');
    });
}

function deleteNotification(notificationId, btn) {
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    fetch('/notifications/' + notificationId, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const notificationRow = btn.closest('.p-4');
            notificationRow.style.transition = 'opacity 0.3s';
            notificationRow.style.opacity = '0';
            setTimeout(() => {
                notificationRow.remove();
                // Check if there are no more notifications
                const remainingNotifications = document.querySelectorAll('.card-body .p-4');
                if (remainingNotifications.length === 0) {
                    location.reload();
                }
            }, 300);
            showToast('Notification deleted', 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            showToast(data.message || 'Failed to delete', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        showToast('An error occurred', 'error');
    });
}

function markAllAsRead() {
    const btn = document.getElementById('mark-all-read-btn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
    
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update all notification rows
            document.querySelectorAll('.card-body .p-4').forEach(row => {
                row.classList.remove('bg-light');
                row.style.background = '';
                const badge = row.querySelector('.badge.bg-primary');
                if (badge) badge.remove();
                const markReadBtn = row.querySelector('button[onclick^="markAsRead"]');
                if (markReadBtn) markReadBtn.remove();
            });
            btn.remove();
            showToast('All notifications marked as read', 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            showToast(data.message || 'Failed to mark all as read', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        showToast('An error occurred', 'error');
    });
}
</script>
@endpush
@endsection
