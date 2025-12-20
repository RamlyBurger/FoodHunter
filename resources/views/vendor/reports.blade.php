@extends('layouts.app')

@section('title', 'Reports & Analytics - Vendor Dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-graph-up text-primary me-2"></i>
                Reports & Analytics
            </h2>
            <p class="text-muted">Track your sales performance and insights</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <form method="GET" action="{{ route('vendor.reports') }}" id="periodForm">
                <div class="d-flex gap-2 justify-content-lg-end">
                    <select class="form-select" name="period" id="periodSelect" style="max-width: 180px;" onchange="handlePeriodChange()">
                        <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                    <div id="customRangeInputs" class="d-none">
                        <input type="date" name="start_date" class="form-control" style="max-width: 150px;" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" style="max-width: 150px;" value="{{ request('end_date') }}">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <i class="bi bi-currency-dollar fs-4 text-primary"></i>
                        </div>
                        @if($revenueStats['growth'] >= 0)
                            <span class="badge bg-success">+{{ number_format($revenueStats['growth'], 1) }}%</span>
                        @else
                            <span class="badge bg-danger">{{ number_format($revenueStats['growth'], 1) }}%</span>
                        @endif
                    </div>
                    <p class="text-muted mb-1 small">Total Revenue</p>
                    <h3 class="mb-0 fw-bold">RM {{ number_format($revenueStats['total'], 2) }}</h3>
                    <small class="text-muted">Completed orders only</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cart-check fs-4 text-success"></i>
                        </div>
                        @if($orderStats['growth'] >= 0)
                            <span class="badge bg-success">+{{ number_format($orderStats['growth'], 1) }}%</span>
                        @else
                            <span class="badge bg-danger">{{ number_format($orderStats['growth'], 1) }}%</span>
                        @endif
                    </div>
                    <p class="text-muted mb-1 small">Total Orders</p>
                    <h3 class="mb-0 fw-bold">{{ $orderStats['total'] }}</h3>
                    <small class="text-muted">{{ $orderStats['completed'] }} completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="bg-info bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cash-coin fs-4 text-info"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-1 small">Avg Order Value</p>
                    <h3 class="mb-0 fw-bold">RM {{ number_format($orderStats['avg_value'], 2) }}</h3>
                    <small class="text-muted">Per completed order</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-star-fill fs-4 text-warning"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-1 small">Total Items</p>
                    <h3 class="mb-0 fw-bold">{{ count($topSellingItems) }}</h3>
                    <small class="text-muted">Top sellers</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Sales Overview</h5>
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="150"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-circle-fill text-success me-2"></i>Completed</span>
                            <strong>{{ $orderStatusDistribution['completed']['percentage'] }}%</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-circle-fill text-info me-2"></i>Accepted</span>
                            <strong>{{ $orderStatusDistribution['accepted']['percentage'] }}%</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-circle-fill text-primary me-2"></i>Preparing</span>
                            <strong>{{ $orderStatusDistribution['preparing']['percentage'] }}%</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-circle-fill text-warning me-2"></i>Ready</span>
                            <strong>{{ $orderStatusDistribution['ready']['percentage'] }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Top Selling Items</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($topSellingItems as $index => $item)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <span class="fw-bold text-primary">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <small class="text-muted">{{ $item->total_quantity }} sold</small>
                                    </div>
                                </div>
                                <span class="badge bg-success">RM {{ number_format($item->total_sales, 2) }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 px-0">
                            <p class="text-muted mb-0">No sales data available</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Recent Reviews</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Ahmad" class="rounded-circle me-2" width="32" height="32">
                                <strong>Ahmad Abdullah</strong>
                            </div>
                            <div>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                        </div>
                        <p class="mb-1 small">"Excellent food! The Nasi Lemak is amazing and authentic."</p>
                        <small class="text-muted">2 hours ago</small>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Siti" class="rounded-circle me-2" width="32" height="32">
                                <strong>Siti Nurhaliza</strong>
                            </div>
                            <div>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star text-muted"></i>
                            </div>
                        </div>
                        <p class="mb-1 small">"Good taste, but portion could be bigger."</p>
                        <small class="text-muted">5 hours ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script id="sales-data" type="application/json">@json($salesChartData)</script>
<script id="status-data" type="application/json">@json($orderStatusDistribution)</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart Data from Backend
    const salesData = JSON.parse(document.getElementById('sales-data').textContent);
    
    // Sales Overview Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.map(d => d.date),
            datasets: [{
                label: 'Revenue (RM)',
                data: salesData.map(d => d.revenue),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Orders',
                data: salesData.map(d => d.orders),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 0) {
                                    label += 'RM ' + context.parsed.y.toFixed(2);
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue (RM)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(0);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Orders'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // Order Status Distribution Chart
    const statusData = JSON.parse(document.getElementById('status-data').textContent);
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Accepted', 'Preparing', 'Ready'],
            datasets: [{
                data: [
                    statusData.completed.count,
                    statusData.accepted.count,
                    statusData.preparing.count,
                    statusData.ready.count
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(23, 162, 184)',
                    'rgb(13, 110, 253)',
                    'rgb(255, 193, 7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
});

// Handle period change
function handlePeriodChange() {
    const period = document.getElementById('periodSelect').value;
    const customInputs = document.getElementById('customRangeInputs');
    
    if (period === 'custom') {
        customInputs.classList.remove('d-none');
        customInputs.classList.add('d-flex');
        customInputs.classList.add('gap-2');
    } else {
        customInputs.classList.add('d-none');
        customInputs.classList.remove('d-flex');
        document.getElementById('periodForm').submit();
    }
}

// Show custom inputs if period is custom on page load
document.addEventListener('DOMContentLoaded', function() {
    const period = document.getElementById('periodSelect').value;
    if (period === 'custom') {
        const customInputs = document.getElementById('customRangeInputs');
        customInputs.classList.remove('d-none');
        customInputs.classList.add('d-flex');
        customInputs.classList.add('gap-2');
    }
});
</script>
@endpush
