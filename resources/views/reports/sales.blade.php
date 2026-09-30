@extends('layouts.app')

@section('title', 'Sales Report')
@section('page_title', 'Wholesale Sales Report')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Sales Report</h5>
            <p class="text-muted small mb-0">Itemized breakdown of wholesale order sales between selected dates</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i> Print View
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-3 mb-4 d-print-none">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Product</label>
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Generate</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-custom p-3 text-center">
                <span class="text-secondary small fw-medium">Distinct Orders</span>
                <h4 class="fw-bold mb-0 text-primary">{{ number_format($uniqueOrdersCount) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 text-center">
                <span class="text-secondary small fw-medium">Total Units Sold</span>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalQty) }} Pcs</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 text-center">
                <span class="text-secondary small fw-medium">Total Net Sales</span>
                <h4 class="fw-bold mb-0 text-success">@currency($totalSales)</h4>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('orders.show', $item->order) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $item->order->order_number }}
                                </a>
                            </td>
                            <td class="text-nowrap text-secondary small">{{ $item->order->order_date->format('d M Y') }}</td>
                            <td>
                                <div class="fw-medium text-dark">{{ $item->order->customer->name }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->product->name }}</div>
                                <code class="small text-muted">{{ $item->product->sku }}</code>
                            </td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end">@currency($item->unit_price)</td>
                            <td class="text-end text-muted">{{ $item->discount > 0 ? '₹' . number_format($item->discount, 2) : '—' }}</td>
                            <td class="text-end fw-bold text-dark">@currency($item->subtotal)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No sales records found for the selected parameters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($items->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total:</td>
                            <td class="text-center">{{ number_format($totalQty) }}</td>
                            <td></td>
                            <td></td>
                            <td class="text-end text-primary fs-6">@currency($totalSales)</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
