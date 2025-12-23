@extends('layouts.app')

@section('title', 'Manage Vouchers - ' . $vendor->store_name)

@section('content')

<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-ticket-perforated text-primary me-2"></i>
                Voucher Management
            </h2>
            <p class="text-muted">Create and manage discount vouchers for your customers</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <button class="btn btn-primary rounded-pill" onclick="showAddModal()">
                <i class="bi bi-plus-circle me-2"></i> Create Voucher
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Vouchers</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #667eea;">
                            <i class="bi bi-ticket-perforated fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Active</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['active'] }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #28a745;">
                            <i class="bi bi-check-circle fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Redemptions</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['total_usage'] }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #fd7e14;">
                            <i class="bi bi-gift fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('vendor.vouchers') }}">
                <div class="row align-items-center g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search vouchers..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('vendor.vouchers') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 vouchers-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Code</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Discount</th>
                            <th class="py-3">Min Order</th>
                            <th class="py-3">Usage</th>
                            <th class="py-3">Expires</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="voucher-code">{{ $voucher->code }}</span>
                            </td>
                            <td class="py-3">{{ $voucher->name }}</td>
                            <td class="py-3">
                                @if($voucher->type === 'percentage')
                                    <strong>{{ number_format($voucher->value) }}%</strong>
                                    @if($voucher->max_discount)
                                        <br><small class="text-muted">max RM{{ number_format($voucher->max_discount, 2) }}</small>
                                    @endif
                                @else
                                    <strong>RM {{ number_format($voucher->value, 2) }}</strong>
                                @endif
                            </td>
                            <td class="py-3">RM {{ number_format($voucher->min_order ?? 0, 2) }}</td>
                            <td class="py-3">
                                {{ $voucher->usage_count }}@if($voucher->usage_limit) / {{ $voucher->usage_limit }}@endif
                            </td>
                            <td class="py-3">
                                @if($voucher->expires_at)
                                    @if($voucher->expires_at->isPast())
                                        <span class="text-danger">{{ $voucher->expires_at->format('d M Y') }}</span>
                                    @else
                                        {{ $voucher->expires_at->format('d M Y') }}
                                    @endif
                                @else
                                    <span class="text-muted">No expiry</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if(!$voucher->is_active)
                                    <span class="badge bg-secondary">Inactive</span>
                                @elseif($voucher->expires_at && $voucher->expires_at->isPast())
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td class="py-3 text-end pe-4">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-info btn-action-sm"
                                            data-voucher-id="{{ $voucher->id }}"
                                            onclick="viewVoucher(this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary btn-action-sm"
                                            data-voucher='@json($voucher)'
                                            onclick="editVoucher(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-action-sm {{ $voucher->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            data-voucher-id="{{ $voucher->id }}"
                                            onclick="toggleVoucher(this)">
                                        <i class="bi bi-{{ $voucher->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action-sm"
                                            data-voucher-id="{{ $voucher->id }}"
                                            data-voucher-name="{{ $voucher->name }}"
                                            onclick="deleteVoucher(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-ticket-perforated fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No vouchers found</p>
                                <button class="btn btn-primary btn-sm" onclick="showAddModal()">Create Your First Voucher</button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($vouchers->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $vouchers->firstItem() ?? 0 }} to {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} vouchers
                </small>
                {{ $vouchers->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* Table Font Size */
.vouchers-table {
    font-size: 0.85rem;
}

.btn-action-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    transition: filter 0.15s ease;
}
.btn-action-sm:hover {
    filter: brightness(0.85);
}
.voucher-code {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.9rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
}

/* Custom Modal Styling */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    visibility: hidden;
    opacity: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.custom-modal.show {
    visibility: visible;
    opacity: 1;
}
.modal-backdrop-custom {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}
.modal-content-custom {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalZoomIn 0.3s ease;
}
.modal-content-custom.modal-lg {
    max-width: 600px;
}
@keyframes modalZoomIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.modal-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.9);
    color: #64748b;
    font-size: 1.25rem;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.modal-close-btn:hover {
    background: white;
    color: #1f2937;
    transform: scale(1.1);
}

/* View Modal */
.view-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 20px 20px 0 0;
    color: white;
    text-align: center;
}
.view-card-code {
    font-family: monospace;
    font-size: 2rem;
    font-weight: 700;
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1.5rem;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 0.5rem;
}
.view-card-body {
    padding: 1.5rem;
}
.view-info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.view-info-row:last-child { border-bottom: none; }
.view-info-label { color: #64748b; font-size: 0.9rem; }
.view-info-value { font-weight: 600; color: #1f2937; }

/* Edit Modal */
.edit-modal-header {
    position: relative;
}
.edit-modal-hero {
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.edit-modal-hero i {
    font-size: 2.5rem;
    color: rgba(255,255,255,0.3);
}
.edit-modal-title {
    position: absolute;
    bottom: -18px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    white-space: nowrap;
}
.edit-modal-body {
    padding: 2rem 1.5rem 1.5rem;
}
.edit-modal-footer {
    padding: 0 1.5rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}
.edit-modal-footer .btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 500;
}
</style>
@endpush

<!-- View Modal -->
<div id="viewModal" class="custom-modal">
    <div class="modal-backdrop-custom" onclick="closeViewModal()"></div>
    <div class="modal-content-custom">
        <button class="modal-close-btn" onclick="closeViewModal()"><i class="bi bi-x"></i></button>
        <div id="viewModalContent"></div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="editModal" class="custom-modal">
    <div class="modal-backdrop-custom" onclick="closeEditModal()"></div>
    <div class="modal-content-custom modal-lg">
        <button class="modal-close-btn" onclick="closeEditModal()"><i class="bi bi-x"></i></button>
        <div id="editModalContent"></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentEditId = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // View Modal
    window.viewVoucher = async function(btn) {
        const id = btn.dataset.voucherId;
        document.getElementById('viewModal').classList.add('show');
        document.getElementById('viewModalContent').innerHTML = '<div style="padding: 3rem; text-align: center;"><div class="spinner-border text-primary"></div></div>';

        // Find voucher from table row
        const row = btn.closest('tr');
        const code = row.querySelector('.voucher-code').textContent;
        const name = row.cells[1].textContent;
        const discount = row.cells[2].innerHTML;
        const minOrder = row.cells[3].textContent;
        const usage = row.cells[4].textContent.trim();
        const expires = row.cells[5].textContent.trim();
        const status = row.cells[6].querySelector('.badge').textContent;
        const statusClass = row.cells[6].querySelector('.badge').classList.contains('bg-success') ? 'success' : 
                           row.cells[6].querySelector('.badge').classList.contains('bg-danger') ? 'danger' : 'secondary';

        document.getElementById('viewModalContent').innerHTML = `
            <div class="view-card-header">
                <div class="view-card-code">${escapeHtml(code)}</div>
                <h4 class="mb-0">${escapeHtml(name)}</h4>
            </div>
            <div class="view-card-body">
                <div class="view-info-row">
                    <span class="view-info-label">Status</span>
                    <span class="badge bg-${statusClass}">${status}</span>
                </div>
                <div class="view-info-row">
                    <span class="view-info-label">Discount</span>
                    <span class="view-info-value">${discount}</span>
                </div>
                <div class="view-info-row">
                    <span class="view-info-label">Minimum Order</span>
                    <span class="view-info-value">${minOrder}</span>
                </div>
                <div class="view-info-row">
                    <span class="view-info-label">Usage</span>
                    <span class="view-info-value">${usage}</span>
                </div>
                <div class="view-info-row">
                    <span class="view-info-label">Expires</span>
                    <span class="view-info-value">${expires}</span>
                </div>
            </div>
        `;
    };

    window.closeViewModal = function() {
        document.getElementById('viewModal').classList.remove('show');
    };

    // Add/Edit Modal
    window.showAddModal = function() {
        currentEditId = null;
        document.getElementById('editModalContent').innerHTML = `
            <div class="edit-modal-header">
                <div class="edit-modal-hero"><i class="bi bi-plus-lg"></i></div>
                <div class="edit-modal-title">Create Voucher</div>
            </div>
            <div class="edit-modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Voucher Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-name" class="form-control" placeholder="e.g., Summer Sale">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Code</label>
                        <input type="text" id="edit-code" class="form-control" placeholder="Auto-generated" style="text-transform: uppercase;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea id="edit-description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                        <select id="edit-type" class="form-select">
                            <option value="fixed">Fixed Amount (RM)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Discount Value <span class="text-danger">*</span></label>
                        <input type="number" id="edit-value" class="form-control" placeholder="e.g., 10" step="0.01" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Min Order (RM)</label>
                        <input type="number" id="edit-min-order" class="form-control" placeholder="0" step="0.01" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Max Discount (RM)</label>
                        <input type="number" id="edit-max-discount" class="form-control" placeholder="For %" step="0.01" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Usage Limit</label>
                        <input type="number" id="edit-usage-limit" class="form-control" placeholder="Unlimited" min="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Per User Limit</label>
                        <input type="number" id="edit-per-user" class="form-control" placeholder="1" min="1">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Expires At</label>
                        <input type="datetime-local" id="edit-expires" class="form-control">
                    </div>
                </div>
                <div id="edit-error" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
            <div class="edit-modal-footer">
                <button class="btn btn-light" onclick="closeEditModal()">Cancel</button>
                <button class="btn btn-primary" onclick="submitVoucher()"><i class="bi bi-check-lg me-1"></i> Create</button>
            </div>
        `;
        document.getElementById('editModal').classList.add('show');
    };

    window.editVoucher = function(btn) {
        const voucher = JSON.parse(btn.dataset.voucher);
        currentEditId = voucher.id;
        
        document.getElementById('editModalContent').innerHTML = `
            <div class="edit-modal-header">
                <div class="edit-modal-hero"><i class="bi bi-pencil"></i></div>
                <div class="edit-modal-title">Edit Voucher</div>
            </div>
            <div class="edit-modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Voucher Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-name" class="form-control" value="${escapeHtml(voucher.name)}">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Code</label>
                        <input type="text" id="edit-code" class="form-control" value="${escapeHtml(voucher.code)}" readonly style="background: #f8f9fa;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea id="edit-description" class="form-control" rows="2">${escapeHtml(voucher.description || '')}</textarea>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                        <select id="edit-type" class="form-select">
                            <option value="fixed" ${voucher.type === 'fixed' ? 'selected' : ''}>Fixed Amount (RM)</option>
                            <option value="percentage" ${voucher.type === 'percentage' ? 'selected' : ''}>Percentage (%)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Discount Value <span class="text-danger">*</span></label>
                        <input type="number" id="edit-value" class="form-control" value="${voucher.value}" step="0.01" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Min Order (RM)</label>
                        <input type="number" id="edit-min-order" class="form-control" value="${voucher.min_order || ''}" step="0.01" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Max Discount (RM)</label>
                        <input type="number" id="edit-max-discount" class="form-control" value="${voucher.max_discount || ''}" step="0.01" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Usage Limit</label>
                        <input type="number" id="edit-usage-limit" class="form-control" value="${voucher.usage_limit || ''}" min="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Per User Limit</label>
                        <input type="number" id="edit-per-user" class="form-control" value="${voucher.per_user_limit || 1}" min="1">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Expires At</label>
                        <input type="datetime-local" id="edit-expires" class="form-control" value="${voucher.expires_at ? voucher.expires_at.slice(0, 16) : ''}">
                    </div>
                </div>
                <div id="edit-error" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
            <div class="edit-modal-footer">
                <button class="btn btn-light" onclick="closeEditModal()">Cancel</button>
                <button class="btn btn-primary" onclick="submitVoucher()"><i class="bi bi-check-lg me-1"></i> Update</button>
            </div>
        `;
        document.getElementById('editModal').classList.add('show');
    };

    window.closeEditModal = function() {
        document.getElementById('editModal').classList.remove('show');
        currentEditId = null;
    };

    window.submitVoucher = async function() {
        const errorEl = document.getElementById('edit-error');
        errorEl.style.display = 'none';

        const name = document.getElementById('edit-name').value.trim();
        const type = document.getElementById('edit-type').value;
        const value = document.getElementById('edit-value').value;
        const minOrder = document.getElementById('edit-min-order').value;
        const maxDiscount = document.getElementById('edit-max-discount').value;

        // Frontend validation
        if (!name) {
            errorEl.textContent = 'Voucher name is required.';
            errorEl.style.display = 'block';
            document.getElementById('edit-name').focus();
            return;
        }

        if (!value || parseFloat(value) <= 0) {
            errorEl.textContent = 'Discount value must be greater than 0.';
            errorEl.style.display = 'block';
            document.getElementById('edit-value').focus();
            return;
        }

        // Validate percentage range
        if (type === 'percentage' && parseFloat(value) > 100) {
            errorEl.textContent = 'Percentage discount cannot exceed 100%.';
            errorEl.style.display = 'block';
            document.getElementById('edit-value').focus();
            return;
        }

        // Validate min_order is positive
        if (minOrder && parseFloat(minOrder) < 0) {
            errorEl.textContent = 'Minimum order cannot be negative.';
            errorEl.style.display = 'block';
            return;
        }

        // Validate max_discount is positive
        if (maxDiscount && parseFloat(maxDiscount) < 0) {
            errorEl.textContent = 'Maximum discount cannot be negative.';
            errorEl.style.display = 'block';
            return;
        }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('code', document.getElementById('edit-code').value.trim());
        formData.append('description', document.getElementById('edit-description').value);
        formData.append('type', type);
        formData.append('value', value);
        formData.append('min_order', minOrder || '');
        formData.append('max_discount', maxDiscount || '');
        formData.append('usage_limit', document.getElementById('edit-usage-limit').value || '');
        const perUserValue = document.getElementById('edit-per-user').value;
        formData.append('per_user_limit', perUserValue && perUserValue.trim() !== '' ? perUserValue : '');
        formData.append('expires_at', document.getElementById('edit-expires').value || '');

        if (currentEditId) {
            formData.append('_method', 'PUT');
        }

        const url = currentEditId ? `/vendor/vouchers/${currentEditId}` : '{{ route("vendor.vouchers.store") }}';
        const submitBtn = document.querySelector('.edit-modal-footer .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            let data;
            const contentType = res.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await res.json();
            } else {
                const text = await res.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned an invalid response. Please try again.');
            }

            if (res.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                const errorMessage = data.message || 'Operation failed. Please check your input.';
                errorEl.textContent = errorMessage;
                errorEl.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (e) {
            console.error('Error:', e);
            errorEl.textContent = e.message || 'An error occurred. Please try again.';
            errorEl.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    };

    window.toggleVoucher = async function(btn) {
        const id = btn.dataset.voucherId;
        try {
            const res = await fetch(`/vendor/vouchers/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: data.is_active ? 'Activated!' : 'Deactivated!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            }
        } catch (e) {
            Swal.fire('Error', 'An error occurred', 'error');
        }
    };

    window.deleteVoucher = function(btn) {
        const id = btn.dataset.voucherId;
        const name = btn.dataset.voucherName;
        
        Swal.fire({
            title: 'Delete Voucher?',
            html: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?<br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`/vendor/vouchers/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }
                } catch (e) {
                    Swal.fire('Error', 'An error occurred', 'error');
                }
            }
        });
    };

    // Close modals on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeViewModal();
            closeEditModal();
        }
    });
});
</script>
@endpush
