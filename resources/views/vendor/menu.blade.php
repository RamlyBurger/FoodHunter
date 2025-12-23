@extends('layouts.app')

@section('title', 'My Menu - Vendor Dashboard')

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-collection text-warning me-2"></i>
                My Menu Items
            </h2>
            <p class="text-muted">Manage your food items, prices, and availability</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Items</p>
                            <h3 class="mb-0 fw-bold">{{ $items->total() }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #ffc107;">
                            <i class="bi bi-grid-3x3-gap fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Available</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $items->where('is_available', true)->count() }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #28a745;">
                            <i class="bi bi-check-circle fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Out of Stock</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ $items->where('is_available', false)->count() }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #dc3545;">
                            <i class="bi bi-x-circle fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Categories</p>
                            <h3 class="mb-0 fw-bold">{{ $items->pluck('category_id')->unique()->count() }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #17a2b8;">
                            <i class="bi bi-tags fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('vendor.menu') }}">
                <div class="row align-items-center g-2">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search items..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($items->pluck('category.name')->unique()->filter() as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Available</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="per_page" class="form-select">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
                            <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20 per page</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('vendor.menu') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">ITEM</th>
                            <th>CATEGORY</th>
                            <th>PRICE</th>
                            <th>STATUS</th>
                            <th class="text-end px-4">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        @php
                            $initials = strtoupper(substr($item->name, 0, 2));
                            $colors = ['#ffc107', '#28a745', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c'];
                            $colorIndex = crc32($item->name) % count($colors);
                            $bgColor = $colors[$colorIndex];
                            $catName = $item->category->name ?? 'Uncategorized';
                        @endphp
                        <tr class="menu-item-row" 
                            data-name="{{ strtolower($item->name) }}" 
                            data-category="{{ $catName }}"
                            data-status="{{ $item->is_available ? 'available' : 'unavailable' }}">
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $item->image ? (Str::startsWith($item->image, ['http://', 'https://']) ? $item->image : asset('storage/' . $item->image)) : '' }}" 
                                         class="rounded me-3" 
                                         width="50" height="50" 
                                         alt="{{ $item->name }}" 
                                         style="object-fit: cover;"
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->name) }}&background=f3f4f6&color=9ca3af&size=100';">
                                    <div>
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $categoryColors = [
                                        'Burgers & Sandwiches' => '#28a745',
                                        'Rice & Noodles' => '#fd7e14',
                                        'Local Favorites' => '#ffc107',
                                        'Beverages' => '#17a2b8',
                                        'Desserts' => '#e83e8c',
                                    ];
                                    $catColor = $categoryColors[$catName] ?? '#6c757d';
                                @endphp
                                <span class="badge rounded-pill" style="background: {{ $catColor }}; padding: 0.4rem 0.8rem; font-weight: 500;">{{ $catName }}</span>
                            </td>
                            <td class="fw-bold" style="color: #ffc107;">RM {{ number_format($item->price, 2) }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->is_available ? 'success' : 'danger' }}" style="padding: 0.4rem 0.8rem; font-weight: 500;">
                                    {{ $item->is_available ? 'Available' : 'Out of Stock' }}
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary btn-action-sm" title="View" style="background-color: #17A2B8; border: none;"
                                            data-item-id="{{ $item->id }}"
                                            onclick="viewItem(this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-action-sm" title="Edit" 
                                            data-item-id="{{ $item->id }}"
                                            data-item-name="{{ $item->name }}"
                                            data-item-category="{{ $item->category_id }}"
                                            data-item-price="{{ $item->price }}"
                                            data-item-description="{{ $item->description ?? '' }}"
                                            data-item-available="{{ $item->is_available ? '1' : '0' }}"
                                            data-item-image="{{ $item->image ? (Str::startsWith($item->image, ['http://', 'https://']) ? $item->image : asset('storage/' . $item->image)) : '' }}"
                                            onclick="editItem(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-action-sm" title="Delete" 
                                            data-item-id="{{ $item->id }}"
                                            data-item-name="{{ $item->name }}"
                                            onclick="deleteItem(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No menu items found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} items
                </small>
                {{ $items->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-action-sm {
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    transition: filter 0.15s ease;
}
.btn-action-sm:hover {
    filter: brightness(0.85);
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
    max-width: 650px;
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

/* View Modal Card Style */
.view-card-image {
    width: 100%;
    height: 220px;
    object-fit: contain;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
}
.view-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 2rem 1.5rem 1.5rem;
    background: linear-gradient(transparent, rgba(0,0,0,0.85));
    border-radius: 0 0 0 0;
}
.view-card-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.view-card-badges .badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 50px;
}
.stats-bar {
    display: flex;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin: 1.5rem;
}
.stats-bar .stat-item {
    flex: 1;
}
.stats-bar .stat-label {
    color: rgba(255,255,255,0.7);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.stats-bar .stat-value {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
}
.view-card-body {
    padding: 0 1.5rem 1.5rem;
}
.info-section {
    margin-bottom: 1rem;
}
.info-label {
    color: #94a3b8;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.info-value {
    color: #334155;
    line-height: 1.6;
}
.meta-footer {
    display: flex;
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
    margin-top: 1rem;
}
.meta-footer .meta-item {
    flex: 1;
    text-align: center;
}

/* Edit Modal Styling */
.edit-modal-header {
    position: relative;
    padding: 0;
}
.edit-modal-hero {
    height: 120px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.edit-modal-hero i {
    font-size: 3rem;
    color: rgba(255,255,255,0.3);
}
.edit-modal-title {
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    white-space: nowrap;
}
.edit-modal-body {
    padding: 2.5rem 1.5rem 1.5rem;
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
    const categories = @json(\App\Models\Category::orderBy('name')->get(['id', 'name']));
    
    let currentEditId = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // View Modal Functions
    window.viewItem = async function(btn) {
        const id = btn.dataset.itemId;
        document.getElementById('viewModal').classList.add('show');
        document.getElementById('viewModalContent').innerHTML = '<div style="padding: 3rem; text-align: center;"><div class="spinner-border text-primary"></div></div>';

        try {
            const res = await fetch(`/vendor/menu/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success) {
                const item = data.item;
                const placeholderImg = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&size=400&background=6366f1&color=fff&font-size=0.35`;
                const imageUrl = item.image || placeholderImg;
                
                document.getElementById('viewModalContent').innerHTML = `
                    <div style="position: relative;">
                        <img src="${imageUrl}" class="view-card-image" onerror="this.src='${placeholderImg}'">
                        <div class="view-card-overlay">
                            <h3 class="view-card-title">${escapeHtml(item.name)}</h3>
                            <div class="view-card-badges">
                                <span class="badge" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">${escapeHtml(item.category)}</span>
                                <span class="badge bg-${item.is_available ? 'success' : 'danger'}" style="margin-left: 0.5rem;">${item.is_available ? 'Available' : 'Out of Stock'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="stats-bar">
                        <div class="stat-item">
                            <div class="stat-label">Price</div>
                            <div class="stat-value">RM ${parseFloat(item.price).toFixed(2)}</div>
                        </div>
                        <div class="stat-item" style="text-align: right;">
                            <div class="stat-label">Total Sold</div>
                            <div class="stat-value">${item.total_sold}</div>
                        </div>
                    </div>
                    <div class="view-card-body">
                        <div class="info-section">
                            <div class="info-label">Description</div>
                            <div class="info-value">${item.description || '<em style="color: #94a3b8;">No description available</em>'}</div>
                        </div>
                        <div class="meta-footer">
                            <div class="meta-item">
                                <div class="info-label">Created</div>
                                <div style="color: #475569; font-size: 0.85rem; font-weight: 500;">${item.created_at}</div>
                            </div>
                            <div class="meta-item">
                                <div class="info-label">Updated</div>
                                <div style="color: #475569; font-size: 0.85rem; font-weight: 500;">${item.updated_at}</div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('viewModalContent').innerHTML = '<div style="padding: 2rem; text-align: center; color: #ef4444;">Failed to load item</div>';
            }
        } catch (e) {
            document.getElementById('viewModalContent').innerHTML = '<div style="padding: 2rem; text-align: center; color: #ef4444;">An error occurred</div>';
        }
    };

    window.closeViewModal = function() {
        document.getElementById('viewModal').classList.remove('show');
    };

    // Add/Edit Modal Functions
    window.showAddItemModal = function() {
        currentEditId = null;
        const categoryOptions = categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
        
        document.getElementById('editModalContent').innerHTML = `
            <div class="edit-modal-header">
                <div class="edit-modal-hero"><i class="bi bi-plus-lg"></i></div>
                <div class="edit-modal-title">Add New Item</div>
            </div>
            <div class="edit-modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit-name" class="form-control form-control-lg" placeholder="e.g., Nasi Lemak Special" maxlength="255">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select id="edit-category" class="form-select form-select-lg">
                            <option value="">Select category</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Price (RM) <span class="text-danger">*</span></label>
                        <input type="number" id="edit-price" class="form-control form-control-lg" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea id="edit-description" class="form-control" rows="3" placeholder="Brief description..." maxlength="1000"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Image</label>
                    <input type="file" id="edit-image" class="form-control" accept="image/*">
                    <small class="text-muted">Max 2MB. Formats: JPG, PNG, GIF</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="edit-available" checked style="width: 2.5em; height: 1.25em;">
                    <label class="form-check-label ms-2" for="edit-available">Available for orders</label>
                </div>
                <div id="edit-error" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
            <div class="edit-modal-footer">
                <button class="btn btn-light" onclick="closeEditModal()">Cancel</button>
                <button class="btn btn-primary" onclick="submitForm()"><i class="bi bi-check-lg me-1"></i> Add Item</button>
            </div>
        `;
        document.getElementById('editModal').classList.add('show');
    };

    window.editItem = function(btn) {
        currentEditId = btn.dataset.itemId;
        const name = btn.dataset.itemName;
        const categoryId = btn.dataset.itemCategory;
        const price = btn.dataset.itemPrice;
        const description = btn.dataset.itemDescription;
        const available = btn.dataset.itemAvailable === '1';
        const image = btn.dataset.itemImage || '';

        const categoryOptions = categories.map(cat => 
            `<option value="${cat.id}" ${cat.id == categoryId ? 'selected' : ''}>${cat.name}</option>`
        ).join('');

        const currentImageHtml = image ? `
            <div class="mb-3">
                <label class="form-label fw-semibold">Current Image</label>
                <div><img src="${image}" class="rounded" style="max-height: 80px; object-fit: cover;" onerror="this.parentElement.style.display='none'"></div>
            </div>
        ` : '';
        
        document.getElementById('editModalContent').innerHTML = `
            <div class="edit-modal-header">
                <div class="edit-modal-hero"><i class="bi bi-pencil"></i></div>
                <div class="edit-modal-title">Edit Item</div>
            </div>
            <div class="edit-modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit-name" class="form-control form-control-lg" value="${escapeHtml(name)}" maxlength="255">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select id="edit-category" class="form-select form-select-lg">
                            <option value="">Select category</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Price (RM) <span class="text-danger">*</span></label>
                        <input type="number" id="edit-price" class="form-control form-control-lg" value="${price}" step="0.01" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea id="edit-description" class="form-control" rows="3" maxlength="1000">${escapeHtml(description)}</textarea>
                </div>
                ${currentImageHtml}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Change Image</label>
                    <input type="file" id="edit-image" class="form-control" accept="image/*">
                    <small class="text-muted">Leave empty to keep current image</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="edit-available" ${available ? 'checked' : ''} style="width: 2.5em; height: 1.25em;">
                    <label class="form-check-label ms-2" for="edit-available">Available for orders</label>
                </div>
                <div id="edit-error" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
            <div class="edit-modal-footer">
                <button class="btn btn-light" onclick="closeEditModal()">Cancel</button>
                <button class="btn btn-primary" onclick="submitForm()"><i class="bi bi-check-lg me-1"></i> Update Item</button>
            </div>
        `;
        document.getElementById('editModal').classList.add('show');
    };

    window.closeEditModal = function() {
        document.getElementById('editModal').classList.remove('show');
    };

    window.submitForm = async function() {
        const name = document.getElementById('edit-name').value.trim();
        const category = document.getElementById('edit-category').value;
        const price = document.getElementById('edit-price').value;
        const description = document.getElementById('edit-description').value.trim();
        const image = document.getElementById('edit-image').files[0];
        const available = document.getElementById('edit-available').checked;
        const errorEl = document.getElementById('edit-error');

        errorEl.style.display = 'none';

        if (!name) { errorEl.textContent = 'Item name is required'; errorEl.style.display = 'block'; return; }
        if (!category) { errorEl.textContent = 'Please select a category'; errorEl.style.display = 'block'; return; }
        if (!price || parseFloat(price) < 0) { errorEl.textContent = 'Please enter a valid price'; errorEl.style.display = 'block'; return; }
        if (image && image.size > 2 * 1024 * 1024) { errorEl.textContent = 'Image must be less than 2MB'; errorEl.style.display = 'block'; return; }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('category_id', category);
        formData.append('price', price);
        formData.append('description', description);
        formData.append('is_available', available ? '1' : '0');
        if (image) formData.append('image', image);

        const url = currentEditId ? `/vendor/menu/${currentEditId}` : '{{ route("vendor.menu.store") }}';
        if (currentEditId) formData.append('_method', 'PUT');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                location.reload();
            } else {
                let msg = result.message || 'An error occurred';
                if (result.errors) msg = Object.values(result.errors).flat().join(', ');
                errorEl.textContent = msg;
                errorEl.style.display = 'block';
            }
        } catch (e) {
            errorEl.textContent = 'An unexpected error occurred';
            errorEl.style.display = 'block';
        }
    };

    // Delete Item with SweetAlert
    window.deleteItem = function(btn) {
        const itemId = btn.dataset.itemId;
        const itemName = btn.dataset.itemName;
        
        Swal.fire({
            title: 'Delete Menu Item?',
            html: `Are you sure you want to delete <strong>"${itemName}"</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Yes, delete it',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const res = await fetch(`/vendor/menu/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });
                    const data = await res.json();
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to delete item');
                    }
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(`Request failed: ${error.message}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Menu item has been deleted successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    };

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeViewModal();
            closeEditModal();
        }
    });
});
</script>
@endpush
