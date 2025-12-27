{{--
|==============================================================================
| Notifications Page - Lee Song Yan (Cart, Checkout & Notifications Module)
|==============================================================================
|
| @author     Lee Song Yan
| @module     Cart, Checkout & Notifications Module
| @pattern    Observer Pattern (NotificationObserver)
|
| Displays user notifications with AJAX loading and mark as read.
| Notifications created by Observer Pattern from order events.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-bell me-2" style="color: var(--primary-color);"></i>Notifications</h2>
            <small class="text-muted" id="unread-count-text"></small>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="mark-all-read-btn" onclick="markAllAsRead()" style="display: none;">
            <i class="bi bi-check-all me-1"></i> Mark All as Read
        </button>
    </div>

    <div class="card" id="notifications-card">
        <div class="card-body p-0" id="notifications-container">
            <!-- Loading state -->
            <div class="text-center py-5" id="notifications-loading">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2 mb-0">Loading notifications...</p>
            </div>
        </div>
    </div>

    <div class="mt-4" id="pagination-container" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted" id="pagination-info"></small>
            <nav id="pagination-nav"></nav>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentPage = 1;

    // Notification type icon configuration
    const typeIcons = {
        'order_created': { icon: 'bag-plus', bg: 'rgba(0, 122, 255, 0.15)', color: 'var(--primary-color)' },
        'order_status': { icon: 'arrow-repeat', bg: 'rgba(88, 86, 214, 0.15)', color: 'var(--secondary-color)' },
        'order_completed': { icon: 'bag-check', bg: 'rgba(52, 199, 89, 0.15)', color: 'var(--success-color)' },
        'points_earned': { icon: 'star', bg: 'rgba(255, 149, 0, 0.15)', color: 'var(--warning-color)' },
        'welcome': { icon: 'emoji-smile', bg: 'rgba(0, 122, 255, 0.15)', color: 'var(--primary-color)' },
        'default': { icon: 'bell', bg: 'var(--gray-100)', color: 'var(--gray-500)' }
    };

    // Load notifications via AJAX
    window.loadNotifications = async function(page = 1) {
        currentPage = page;
        const container = document.getElementById('notifications-container');
        
        if (!container) {
            console.error('Notifications container not found');
            return;
        }
        
        // Show loading
        container.innerHTML = `<div class="text-center py-5" id="notifications-loading">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2 mb-0">Loading notifications...</p>
        </div>`;

        try {
            const res = await fetch(`/notifications?page=${page}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const response = await res.json();

            if (response.success) {
                const data = response.data || response;
                renderNotifications(data.notifications || []);
                renderPagination(data.pagination || {});
                updateUnreadCount(data.unread_count || 0);
            } else {
                container.innerHTML = `<div class="text-center py-5 text-danger">Failed to load notifications</div>`;
            }
        } catch (e) {
            console.error('Error loading notifications:', e);
            container.innerHTML = `<div class="text-center py-5 text-danger">An error occurred</div>`;
        }
    };

    // Render notifications list
    function renderNotifications(notifications) {
        const container = document.getElementById('notifications-container');
        
        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: var(--gray-100);">
                        <i class="bi bi-bell-slash" style="font-size: 48px; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="mt-3">No notifications</h4>
                    <p class="text-muted">You're all caught up!</p>
                </div>`;
            document.getElementById('mark-all-read-btn').style.display = 'none';
            return;
        }

        container.innerHTML = notifications.map((n, index) => {
            const iconConfig = typeIcons[n.type] || typeIcons['default'];
            const unreadStyle = !n.is_read ? 'background: rgba(0, 122, 255, 0.03) !important;' : '';
        
        return `
        <div class="p-4 notification-row ${index > 0 ? 'border-top' : ''} ${!n.is_read ? 'bg-light' : ''}" 
             data-notification-id="${n.id}" style="${unreadStyle}">
            <div class="d-flex gap-3">
                <div class="flex-shrink-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 44px; height: 44px; background: ${iconConfig.bg};">
                        <i class="bi bi-${iconConfig.icon}" style="color: ${iconConfig.color};"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <strong style="font-weight: 600;">${escapeHtml(n.title)}</strong>
                        ${!n.is_read ? '<span class="badge bg-primary new-badge" style="font-size: 0.65rem;">New</span>' : ''}
                    </div>
                    <p class="mb-1 text-muted" style="font-size: 0.9rem;">${escapeHtml(n.message)}</p>
                    <small class="text-muted">${n.time_ago}</small>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    ${!n.is_read ? `
                    <button type="button" class="btn btn-sm btn-outline-secondary mark-read-btn" title="Mark as read" 
                            style="padding: 4px 8px;" onclick="markAsRead(${n.id}, this)">
                        <i class="bi bi-check"></i>
                    </button>` : ''}
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" 
                            style="padding: 4px 8px;" onclick="deleteNotification(${n.id}, this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
        }).join('');
    }

    // Render pagination
    function renderPagination(pagination) {
        const container = document.getElementById('pagination-container');
        const info = document.getElementById('pagination-info');
        const nav = document.getElementById('pagination-nav');

        if (!pagination || pagination.total === 0 || pagination.last_page <= 1) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        info.textContent = `Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total} notifications`;

        let paginationHtml = '<ul class="pagination pagination-sm mb-0">';
        
        paginationHtml += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadNotifications(${pagination.current_page - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>`;

        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 1 && i <= pagination.current_page + 1)) {
                paginationHtml += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadNotifications(${i})">${i}</a>
                </li>`;
            } else if (i === pagination.current_page - 2 || i === pagination.current_page + 2) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        paginationHtml += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadNotifications(${pagination.current_page + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>`;

        paginationHtml += '</ul>';
        nav.innerHTML = paginationHtml;
    }

    function updateUnreadCount(count) {
        const countText = document.getElementById('unread-count-text');
        const markAllBtn = document.getElementById('mark-all-read-btn');
        
        if (count > 0) {
            countText.textContent = `${count} unread notification${count > 1 ? 's' : ''}`;
            markAllBtn.style.display = 'inline-block';
        } else {
            countText.textContent = 'All caught up!';
            markAllBtn.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    window.markAsRead = function(notificationId, btn) {
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
                const responseData = data.data || data;
                const notificationRow = btn.closest('.p-4');
                notificationRow.classList.remove('bg-light');
                notificationRow.style.background = '';
                const badge = notificationRow.querySelector('.badge.bg-primary');
                if (badge) badge.remove();
                btn.remove();
                
                if (responseData.unread_count !== undefined) {
                    updateUnreadCount(responseData.unread_count);
                }
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
    };

    window.deleteNotification = function(notificationId, btn) {
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
                const responseData = data.data || data;
                const notificationRow = btn.closest('.p-4');
                notificationRow.style.transition = 'opacity 0.3s';
                notificationRow.style.opacity = '0';
                setTimeout(() => {
                    notificationRow.remove();
                    
                    if (responseData.unread_count !== undefined) {
                        updateUnreadCount(responseData.unread_count);
                    }
                    
                    const remainingNotifications = document.querySelectorAll('#notifications-container .p-4');
                    if (remainingNotifications.length === 0) {
                        if (currentPage > 1) {
                            loadNotifications(currentPage - 1);
                        } else {
                            loadNotifications(1);
                        }
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
    };

    window.markAllAsRead = function() {
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
                document.querySelectorAll('#notifications-container .p-4').forEach(row => {
                    row.classList.remove('bg-light');
                    row.style.background = '';
                    const badge = row.querySelector('.badge.bg-primary');
                    if (badge) badge.remove();
                    const markReadBtn = row.querySelector('.mark-read-btn');
                    if (markReadBtn) markReadBtn.remove();
                });
                
                updateUnreadCount(0);
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
    };

    // Load notifications on page load
    loadNotifications(1);
});
</script>
@endpush
@endsection
