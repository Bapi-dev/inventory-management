@extends('layouts.app')

@section('title', 'Stock Movement History')
@section('page_title', 'Stock Movements Log')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Stock Movement Audit Trail</h5>
            <p class="text-muted small mb-0">Complete historical record of all inward, outward, sales, and adjustment movements</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Stock Overview
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('inventory.movements') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Product</label>
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Movement Type</label>
                <select name="movement_type" class="form-select form-select-sm">
                    <option value="">All Movement Types</option>
                    <option value="opening_stock" {{ request('movement_type') === 'opening_stock' ? 'selected' : '' }}>Opening Stock</option>
                    <option value="stock_in" {{ request('movement_type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                    <option value="sale" {{ request('movement_type') === 'sale' ? 'selected' : '' }}>Sale (Order Deduction)</option>
                    <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    <option value="return" {{ request('movement_type') === 'return' ? 'selected' : '' }}>Return</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                @if(request()->hasAny(['product_id', 'movement_type', 'from_date', 'to_date']))
                    <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Product & SKU</th>
                        <th class="text-center">Movement Type</th>
                        <th class="text-center">Quantity</th>
                        <th>Reference</th>
                        <th>Logged By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td class="small text-nowrap text-secondary">{{ $m->date->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $m->product->name }}</div>
                                <code class="small text-primary">{{ $m->product->sku }}</code>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $m->movement_badge_class }}">
                                    {{ $m->movement_type_label }}
                                </span>
                            </td>
                            <td class="text-center fw-bold {{ $m->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }} {{ $m->product->unit }}
                            </td>
                            <td class="small">
                                @if($m->reference_type === 'Order' && $m->reference_id)
                                    <a href="{{ route('orders.show', $m->reference_id) }}" class="text-primary text-decoration-none">
                                        Order #{{ $m->reference_id }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $m->reference_type ?? 'Manual' }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $m->creator->name ?? 'System' }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 250px;">
                                {{ $m->notes ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-50"></i>
                                No stock movements recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
