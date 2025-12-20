@extends('layouts.app')

@section('title', 'My Menu - Vendor Dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-collection text-primary me-2"></i>
                My Menu Items
            </h2>
            <p class="text-muted">Manage your food items, prices, and availability</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Item
            </button>
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
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-grid-3x3-gap fs-4 text-primary"></i>
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
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['available'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
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
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['unavailable'] }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-x-circle fs-4 text-danger"></i>
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
                            <h3 class="mb-0 fw-bold">{{ $stats['categories'] }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-tag fs-4 text-info"></i>
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
                        <input type="text" name="search" class="form-control" placeholder="Search items..." value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}" {{ ($category ?? '') == $cat->category_id ? 'selected' : '' }}>
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Available</option>
                            <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="per_page" class="form-select">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
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
                            <th class="px-4">Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Sales</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menuItems as $item)
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($item->image_path) }}" class="rounded me-3" width="50" height="50" alt="{{ $item->name }}" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary">{{ $item->category->category_name }}</span></td>
                            <td class="fw-bold text-success">RM {{ number_format($item->price, 2) }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input availability-toggle" type="checkbox" 
                                           data-item-id="{{ $item->item_id }}" 
                                           {{ $item->is_available ? 'checked' : '' }}>
                                    <label class="form-check-label small {{ $item->is_available ? 'text-success' : 'text-danger' }}">
                                        {{ $item->is_available ? 'Available' : 'Out of Stock' }}
                                    </label>
                                </div>
                            </td>
                            <td>
                                @if($item->total_sold > 0)
                                    <div>
                                        <span class="badge bg-success">{{ $item->total_sold }} sold</span>
                                        <br>
                                        <small class="text-muted">RM {{ number_format($item->total_revenue, 2) }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end px-4">
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->item_id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('vendor.menu.destroy', $item->item_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No menu items found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $menuItems->firstItem() ?? 0 }} to {{ $menuItems->lastItem() ?? 0 }} of {{ $menuItems->total() }} items
                </small>
                {{ $menuItems->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('vendor.menu.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Nasi Lemak Special" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the item"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_available" value="1" id="addAvailable" checked>
                                <label class="form-check-label" for="addAvailable">
                                    Available for orders
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modals -->
@foreach($menuItems as $item)
<div class="modal fade" id="editItemModal{{ $item->item_id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('vendor.menu.update', $item->item_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->category_id }}" {{ $item->category_id == $cat->category_id ? 'selected' : '' }}>
                                        {{ $cat->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" value="{{ $item->price }}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $item->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Current Image</label>
                            <div class="mb-2">
                                <img src="{{ asset($item->image_path) }}" class="rounded" width="100" alt="{{ $item->name }}">
                            </div>
                            <label class="form-label">Change Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_available" value="1" 
                                       id="editAvailable{{ $item->item_id }}" {{ $item->is_available ? 'checked' : '' }}>
                                <label class="form-check-label" for="editAvailable{{ $item->item_id }}">
                                    Available for orders
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
.table img {
    object-fit: cover;
}
</style>
@endpush

@push('scripts')
<script>
// AJAX availability toggle
document.querySelectorAll('.availability-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const itemId = this.dataset.itemId;
        const label = this.nextElementSibling;
        
        fetch(`/vendor/menu/${itemId}/toggle-availability`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.is_available) {
                    label.textContent = 'Available';
                    label.classList.remove('text-danger');
                    label.classList.add('text-success');
                } else {
                    label.textContent = 'Out of Stock';
                    label.classList.remove('text-success');
                    label.classList.add('text-danger');
                }
            } else {
                this.checked = !this.checked;
                alert('Failed to update availability');
            }
        })
        .catch(error => {
            this.checked = !this.checked;
            console.error('Error:', error);
        });
    });
});
</script>
@endpush
