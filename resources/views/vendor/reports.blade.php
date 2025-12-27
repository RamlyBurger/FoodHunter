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
                <select class="form-select form-select-sm" id="period-select" onchange="loadReports()" style="min-width: 150px;">
                    <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
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
let salesData = @json($salesChartData);
let currentChart = null;
let currentView = 'revenue';
let isLoading = false;

// Load reports via AJAX
window.loadReports = async function() {
    if (isLoading) return;
    isLoading = true;
    
    const period = document.getElementById('period-select').value;
    
    // Show loading state
    showLoadingState();
    
    try {
        const res = await fetch(`/vendor/reports?period=${period}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const response = await res.json();

        if (response.success) {
            const data = response.data || response;
            updateRevenueStats(data.revenueStats);
            updateOrderStats(data.orderStats);
            updateOrderStatusDistribution(data.orderStatusDistribution);
            updateTopSellingItems(data.topSellingItems);
            updateSalesChart(data.salesChartData);
            salesData = data.salesChartData;
        }
    } catch (e) {
        console.error('Error loading reports:', e);
    } finally {
        isLoading = false;
    }
};

function showLoadingState() {
    // Add subtle loading indicator to stats cards
    document.querySelectorAll('.row.g-4.mb-4 .card-body h3').forEach(el => {
        el.style.opacity = '0.5';
    });
}

function updateRevenueStats(stats) {
    if (!stats) return;
    const card = document.querySelector('.col-md-3:nth-child(1) .card-body');
    if (card) {
        const h3 = card.querySelector('h3');
        if (h3) {
            h3.textContent = `RM ${parseFloat(stats.total).toFixed(2)}`;
            h3.style.opacity = '1';
        }
        
        // Update growth indicator
        let growthHtml = '';
        if (stats.growth != 0) {
            const isPositive = stats.growth > 0;
            growthHtml = `<small class="${isPositive ? 'text-success' : 'text-danger'}">
                <i class="bi bi-${isPositive ? 'arrow-up' : 'arrow-down'}"></i>
                ${Math.abs(stats.growth)}% vs previous period
            </small>`;
        }
        
        const existingSmall = card.querySelector('small');
        if (existingSmall) existingSmall.remove();
        if (growthHtml) {
            const div = card.querySelector('div > div:first-child');
            if (div) div.insertAdjacentHTML('beforeend', growthHtml);
        }
    }
}

function updateOrderStats(stats) {
    if (!stats) return;
    
    // Total Orders card
    const ordersCard = document.querySelector('.col-md-3:nth-child(2) .card-body');
    if (ordersCard) {
        const h3 = ordersCard.querySelector('h3');
        if (h3) {
            h3.textContent = stats.total;
            h3.style.opacity = '1';
        }
        
        let growthHtml = '';
        if (stats.growth != 0) {
            const isPositive = stats.growth > 0;
            growthHtml = `<small class="${isPositive ? 'text-success' : 'text-danger'}">
                <i class="bi bi-${isPositive ? 'arrow-up' : 'arrow-down'}"></i>
                ${Math.abs(stats.growth)}% vs previous period
            </small>`;
        }
        
        const existingSmall = ordersCard.querySelector('small');
        if (existingSmall) existingSmall.remove();
        if (growthHtml) {
            const div = ordersCard.querySelector('div > div:first-child');
            if (div) div.insertAdjacentHTML('beforeend', growthHtml);
        }
    }
    
    // Avg Order Value card
    const avgCard = document.querySelector('.col-md-3:nth-child(3) .card-body');
    if (avgCard) {
        const h3 = avgCard.querySelector('h3');
        if (h3) {
            h3.textContent = `RM ${parseFloat(stats.avg_value || 0).toFixed(2)}`;
            h3.style.opacity = '1';
        }
    }
    
    // Completion Rate card
    const rateCard = document.querySelector('.col-md-3:nth-child(4) .card-body');
    if (rateCard) {
        const h3 = rateCard.querySelector('h3');
        const completionRate = stats.total > 0 ? ((stats.completed / stats.total) * 100).toFixed(1) : 0;
        if (h3) {
            h3.textContent = `${completionRate}%`;
            h3.style.opacity = '1';
        }
        
        const small = rateCard.querySelector('small');
        if (small) small.textContent = `${stats.completed}/${stats.total} orders`;
    }
}

function updateOrderStatusDistribution(distribution) {
    if (!distribution) return;
    
    const statuses = ['completed', 'confirmed', 'preparing', 'ready', 'cancelled'];
    const colors = { completed: 'success', confirmed: 'primary', preparing: 'info', ready: 'warning', cancelled: 'danger' };
    
    statuses.forEach(status => {
        const data = distribution[status];
        if (!data) return;
        
        // Find the progress bar container by status name
        const containers = document.querySelectorAll('.col-md-4 .card-body .mb-3, .col-md-4 .card-body > div:last-child');
        containers.forEach(container => {
            const label = container.querySelector('.small.fw-medium');
            if (label && label.textContent.toLowerCase() === status) {
                const countEl = container.querySelector(`.text-${colors[status]}`);
                if (countEl) countEl.textContent = data.count;
                
                const progressBar = container.querySelector('.progress-bar');
                if (progressBar) progressBar.style.width = `${data.percentage}%`;
            }
        });
    });
}

function updateTopSellingItems(items) {
    const tbody = document.querySelector('.table-responsive tbody');
    if (!tbody) return;
    
    if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3 mb-0">No sales data available for this period</p>
        </td></tr>`;
        return;
    }
    
    tbody.innerHTML = items.map((item, index) => {
        let rankBadge = '';
        if (index < 3) {
            const badgeClass = index === 0 ? 'bg-warning text-dark' : (index === 1 ? 'bg-secondary' : 'bg-danger');
            rankBadge = `<span class="badge rounded-pill ${badgeClass}" style="width: 28px; height: 28px; line-height: 20px;">${index + 1}</span>`;
        } else {
            rankBadge = `<span class="text-muted">${index + 1}</span>`;
        }
        
        return `<tr>
            <td class="text-center">${rankBadge}</td>
            <td><strong class="text-dark">${item.name}</strong></td>
            <td>RM ${parseFloat(item.price).toFixed(2)}</td>
            <td class="text-center"><span class="badge bg-light text-dark">${item.total_quantity}</span></td>
            <td class="text-center">${item.order_count}</td>
            <td class="text-end"><strong style="color: #FF6B35;">RM ${parseFloat(item.total_sales).toFixed(2)}</strong></td>
        </tr>`;
    }).join('');
}

function updateSalesChart(chartData) {
    if (!currentChart || !chartData) return;
    
    currentChart.data.labels = chartData.map(d => d.date);
    
    if (currentView === 'revenue') {
        currentChart.data.datasets[0].data = chartData.map(d => d.revenue);
    } else {
        currentChart.data.datasets[0].data = chartData.map(d => d.orders);
    }
    
    currentChart.update();
}

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
