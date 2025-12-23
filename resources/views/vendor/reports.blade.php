@extends('layouts.app')

@section('title', 'Reports & Analytics - Vendor Dashboard')

@push('styles')
<style>
@media print {
    .btn, .form-select, nav, footer, .navbar {
        display: none !important;
    }
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    body {
        zoom: 0.8;
    }
}
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-graph-up text-primary me-2"></i>
                Reports & Analytics
            </h2>
            <p class="text-muted">Track your sales performance and insights</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-flex gap-2 justify-content-end align-items-center">
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <form method="GET" action="{{ route('vendor.reports') }}" id="periodForm">
                    <select class="form-select form-select-sm" name="period" onchange="this.form.submit()" style="min-width: 150px;">
                        <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Revenue Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Revenue</p>
                            <h3 class="mb-0 fw-bold text-success">RM {{ number_format($revenueStats['total'], 2) }}</h3>
                            @if($revenueStats['growth'] != 0)
                            <small class="{{ $revenueStats['growth'] > 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-{{ $revenueStats['growth'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($revenueStats['growth']) }}% vs previous period
                            </small>
                            @endif
                        </div>
                        <div class="rounded p-3" style="background: #28a745;">
                            <i class="bi bi-currency-dollar fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Orders</p>
                            <h3 class="mb-0 fw-bold">{{ $orderStats['total'] }}</h3>
                            @if($orderStats['growth'] != 0)
                            <small class="{{ $orderStats['growth'] > 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-{{ $orderStats['growth'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($orderStats['growth']) }}% vs previous period
                            </small>
                            @endif
                        </div>
                        <div class="rounded p-3" style="background: #ffc107;">
                            <i class="bi bi-cart-check fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Avg Order Value</p>
                            <h3 class="mb-0 fw-bold">RM {{ number_format($orderStats['avg_value'], 2) }}</h3>
                        </div>
                        <div class="rounded p-3" style="background: #17a2b8;">
                            <i class="bi bi-cash-coin fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Completion Rate</p>
                            @php
                                $completionRate = $orderStats['total'] > 0 ? round(($orderStats['completed'] / $orderStats['total']) * 100, 1) : 0;
                            @endphp
                            <h3 class="mb-0 fw-bold text-info">{{ $completionRate }}%</h3>
                            <small class="text-muted">{{ $orderStats['completed'] }}/{{ $orderStats['total'] }} orders</small>
                        </div>
                        <div class="rounded p-3" style="background: #17a2b8;">
                            <i class="bi bi-check-circle fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Overview</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateChart('revenue')">Revenue</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('orders')">Orders</button>
                    </div>
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium">Completed</span>
                        <span class="small text-success fw-bold">{{ $orderStatusDistribution['completed']['count'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $orderStatusDistribution['completed']['percentage'] }}%; border-radius: 4px;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium">Confirmed</span>
                        <span class="small text-primary fw-bold">{{ $orderStatusDistribution['confirmed']['count'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-primary" style="width: {{ $orderStatusDistribution['confirmed']['percentage'] }}%; border-radius: 4px;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium">Preparing</span>
                        <span class="small text-info fw-bold">{{ $orderStatusDistribution['preparing']['count'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-info" style="width: {{ $orderStatusDistribution['preparing']['percentage'] }}%; border-radius: 4px;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium">Ready</span>
                        <span class="small text-warning fw-bold">{{ $orderStatusDistribution['ready']['count'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-warning" style="width: {{ $orderStatusDistribution['ready']['percentage'] }}%; border-radius: 4px;"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium">Cancelled</span>
                        <span class="small text-danger fw-bold">{{ $orderStatusDistribution['cancelled']['count'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-danger" style="width: {{ $orderStatusDistribution['cancelled']['percentage'] }}%; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Top Selling Items -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Selling Items</h5>
                </div>
                <div class="card-body p-0">
                @if($topSellingItems->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0">No sales data available for this period</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>Item Name</th>
                                    <th>Price</th>
                                    <th class="text-center">Qty Sold</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topSellingItems as $index => $item)
                                    <tr>
                                        <td class="text-center">
                                            @if($index < 3)
                                                <span class="badge rounded-pill {{ $index === 0 ? 'bg-warning text-dark' : ($index === 1 ? 'bg-secondary' : 'bg-danger') }}" style="width: 28px; height: 28px; line-height: 20px;">
                                                    {{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="text-muted">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td><strong class="text-dark">{{ $item->name }}</strong></td>
                                        <td>RM {{ number_format($item->price, 2) }}</td>
                                        <td class="text-center"><span class="badge bg-light text-dark">{{ $item->total_quantity }}</span></td>
                                        <td class="text-center">{{ $item->order_count }}</td>
                                        <td class="text-end"><strong style="color: #FF6B35;">RM {{ number_format($item->total_sales, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const salesData = @json($salesChartData);
let currentChart = null;
let currentView = 'revenue';

function initChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    currentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: salesData.map(d => d.date),
            datasets: [{
                label: 'Revenue (RM)',
                data: salesData.map(d => d.revenue),
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return currentView === 'revenue' ? 'RM ' + value : value;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (currentView === 'revenue') {
                                return 'Revenue: RM ' + context.raw.toFixed(2);
                            } else {
                                return 'Orders: ' + context.raw;
                            }
                        }
                    }
                }
            }
        }
    });
}

function updateChart(view) {
    currentView = view;
    
    // Update button states
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Update chart data
    if (view === 'revenue') {
        currentChart.data.datasets[0].label = 'Revenue (RM)';
        currentChart.data.datasets[0].data = salesData.map(d => d.revenue);
        currentChart.data.datasets[0].backgroundColor = 'rgba(13, 110, 253, 0.7)';
        currentChart.data.datasets[0].borderColor = 'rgba(13, 110, 253, 1)';
    } else {
        currentChart.data.datasets[0].label = 'Number of Orders';
        currentChart.data.datasets[0].data = salesData.map(d => d.orders);
        currentChart.data.datasets[0].backgroundColor = 'rgba(255, 193, 7, 0.7)';
        currentChart.data.datasets[0].borderColor = 'rgba(255, 193, 7, 1)';
    }
    
    currentChart.options.scales.y.ticks.callback = function(value) {
        return view === 'revenue' ? 'RM ' + value : value;
    };
    
    currentChart.update();
}

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});
</script>
@endpush
@endsection
