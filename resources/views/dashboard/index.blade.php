@extends('layouts.app')

@section('title', 'Business Dashboard')
@section('page_title', 'Wholesale Operations Dashboard')

@section('content')
<div class="container-fluid px-0">

    <!-- Top Action / Greeting Banner -->
    <div class="card card-custom p-4 mb-4 border-0" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
        <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <div class="badge bg-primary px-3 py-2 mb-2"><i class="bi bi-shop me-1"></i> Bags Manufacturing & Wholesale</div>
                <h3 class="fw-bold mb-1">Welcome back, {{ Auth::user()->name }}!</h3>
                <p class="text-white-50 mb-0">Here is the real-time summary of your wholesale orders, inventory levels, and customer receivables.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <a href="{{ route('orders.create') }}" class="btn btn-primary shadow-sm">
                        <i class="bi bi-cart-plus-fill me-1"></i> New Order
                    </a>
                    <a href="{{ route('payments.create') }}" class="btn btn-outline-light">
                        <i class="bi bi-cash-stack me-1"></i> Receive Payment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 8 KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Products -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #3b82f6;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Total Products</span>
                    <span class="p-2 bg-primary bg-opacity-10 text-primary rounded"><i class="bi bi-boxes"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalProducts) }}</h4>
                <div class="small text-muted mt-1">Catalog items</div>
            </div>
        </div>

        <!-- 2. Current Stock Qty -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #10b981;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Stock On Hand</span>
                    <span class="p-2 bg-success bg-opacity-10 text-success rounded"><i class="bi bi-layers-fill"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($currentStockQty) }} <span class="fs-6 fw-normal text-muted">Pcs</span></h4>
                <div class="small text-muted mt-1">Across all categories</div>
            </div>
        </div>

        <!-- 3. Today's Orders -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #6366f1;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Today's Orders</span>
                    <span class="p-2 bg-indigo bg-opacity-10 text-primary rounded"><i class="bi bi-cart-check-fill"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($todaysOrdersCount) }}</h4>
                <div class="small text-muted mt-1">Booked today</div>
            </div>
        </div>

        <!-- 4. Pending Orders -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #f59e0b;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Pending Orders</span>
                    <span class="p-2 bg-warning bg-opacity-10 text-warning rounded"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($pendingOrdersCount) }}</h4>
                <div class="small text-muted mt-1">Draft / Confirmed / Packed</div>
            </div>
        </div>

        <!-- 5. Today's Sales -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #06b6d4;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Today's Sales</span>
                    <span class="p-2 bg-info bg-opacity-10 text-info rounded"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">@currency($todaysSales)</h4>
                <div class="small text-muted mt-1">Confirmed orders today</div>
            </div>
        </div>

        <!-- 6. Total Outstanding -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #ef4444;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Total Outstanding</span>
                    <span class="p-2 bg-danger bg-opacity-10 text-danger rounded"><i class="bi bi-exclamation-circle-fill"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-danger">@currency($totalOutstanding)</h4>
                <div class="small text-muted mt-1">Customer receivables</div>
            </div>
        </div>

        <!-- 7. Payments Received Today -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #8b5cf6;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Payments Today</span>
                    <span class="p-2 bg-purple bg-opacity-10 text-primary rounded"><i class="bi bi-cash-coin"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">@currency($paymentsReceivedToday)</h4>
                <div class="small text-muted mt-1">Cash & transfers collected</div>
            </div>
        </div>

        <!-- 8. Low Stock Products -->
        <div class="col-6 col-md-3">
            <div class="card card-custom metric-card p-3 h-100" style="border-left-color: #dc2626;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Low Stock Alerts</span>
                    <span class="p-2 bg-danger bg-opacity-10 text-danger rounded"><i class="bi bi-shield-exclamation"></i></span>
                </div>
                <h4 class="fw-bold mb-0 {{ $lowStockProductsCount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($lowStockProductsCount) }}</h4>
                <div class="small text-muted mt-1">At or below reorder level</div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Sales Summary & 7-Day Trend Chart -->
    <div class="row g-4 mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-lg-8">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">7-Day Sales Performance</h6>
                        <small class="text-muted">Daily wholesale booking totals</small>
                    </div>
                    <span class="badge bg-light text-dark border">Auto-calculated</span>
                </div>
                <div style="height: 250px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sales Summary Snapshot -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 h-100">
                <h6 class="fw-bold mb-3 text-dark">Sales Summary</h6>

                <div class="p-3 mb-3 rounded bg-light border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted fw-semibold">TODAY</span>
                            <h5 class="fw-bold mb-0 text-primary">@currency($salesToday)</h5>
                        </div>
                        <i class="bi bi-calendar2-day fs-3 text-primary opacity-50"></i>
                    </div>
                </div>

                <div class="p-3 mb-3 rounded bg-light border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted fw-semibold">THIS WEEK</span>
                            <h5 class="fw-bold mb-0 text-success">@currency($salesThisWeek)</h5>
                        </div>
                        <i class="bi bi-calendar-week fs-3 text-success opacity-50"></i>
                    </div>
                </div>

                <div class="p-3 rounded bg-light border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted fw-semibold">THIS MONTH</span>
                            <h5 class="fw-bold mb-0 text-dark">@currency($salesThisMonth)</h5>
                        </div>
                        <i class="bi bi-calendar-month fs-3 text-dark opacity-50"></i>
                    </div>
                </div>

                <div class="mt-4 pt-2 border-top">
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary btn-sm w-100">
                        View Detailed Sales Report <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Orders, Top Outstanding Customers & Low Stock Alerts -->
    <div class="row g-4">
        <!-- Recent Orders (8 Cols) -->
        <div class="col-lg-8">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-1 text-primary"></i> Recent Wholesale Orders</h6>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-light border text-secondary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Order No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">Order Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="fw-semibold text-primary text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-truncate" style="max-width: 170px;">{{ $order->customer->name }}</div>
                                        <small class="text-muted">{{ ucfirst($order->customer->customer_type) }}</small>
                                    </td>
                                    <td class="text-nowrap text-secondary small">{{ $order->order_date->format('d M Y') }}</td>
                                    <td class="text-end fw-semibold">@currency($order->grand_total)</td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->payment_badge_class }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->status_badge_class }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        No recent orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Outstanding & Low Stock (4 Cols) -->
        <div class="col-lg-4 d-flex flex-column gap-4">
            <!-- Top Outstanding Customers -->
            <div class="card card-custom flex-grow-1">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-exclamation-circle text-danger me-1"></i> Top Outstanding</h6>
                    <a href="{{ route('outstanding.index') }}" class="btn btn-sm btn-light border text-secondary">Manage</a>
                </div>
                <div class="p-3">
                    @forelse($topOutstandingCustomers as $item)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <a href="{{ route('customers.show', $item['customer']) }}" class="fw-semibold text-dark text-decoration-none d-block text-truncate" style="max-width: 180px;">
                                    {{ $item['customer']->name }}
                                </a>
                                <small class="text-muted">{{ ucfirst($item['customer']->customer_type) }} • {{ $item['customer']->mobile }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger">@currency($item['balance'])</div>
                                <a href="{{ route('payments.create', ['customer_id' => $item['customer']->id]) }}" class="btn btn-sm btn-outline-success py-0 px-2 mt-1" style="font-size: 0.75rem;">Pay</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted small">
                            No customers currently have outstanding balances.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="card card-custom flex-grow-1">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam text-warning me-1"></i> Low Stock Alerts</h6>
                    <a href="{{ route('inventory.index', ['stock_status' => 'low_stock']) }}" class="btn btn-sm btn-light border text-secondary">View Stock</a>
                </div>
                <div class="p-3">
                    @forelse($lowStockProducts as $p)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 180px;">{{ $p->name }}</div>
                                <small class="text-muted">{{ $p->sku }} • Min: {{ $p->min_stock_level }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $p->stock_badge_class }}">{{ $p->current_stock }} {{ $p->unit }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted small">
                            <i class="bi bi-check-circle text-success me-1"></i> All stock levels are healthy!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Sales (₹)',
                    data: {!! json_encode($chartSales) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
