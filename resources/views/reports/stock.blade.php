@extends('layouts.app')

@section('title', 'Stock Report')
@section('page_title', 'Inventory Stock Report')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Inventory Stock Valuation & Movement Report</h5>
            <p class="text-muted small mb-0">Reconciliation of initial stock, additions, confirmed wholesale sales, and current on-hand levels</p>
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

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product & SKU</th>
                        <th>Category</th>
                        <th class="text-center">Opening Stock</th>
                        <th class="text-center">Stock In (+)</th>
                        <th class="text-center">Sold (-)</th>
                        <th class="text-center">Returns (+)</th>
                        <th class="text-center">Adjustments</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $idx => $r)
                        <tr>
                            <td class="text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $r['product']->name }}</div>
                                <code class="small text-primary">{{ $r['product']->sku }}</code>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $r['product']->category->name }}</span></td>
                            <td class="text-center text-muted">{{ $r['opening_stock'] }}</td>
                            <td class="text-center text-success fw-medium">+{{ $r['stock_in'] }}</td>
                            <td class="text-center text-danger fw-medium">-{{ $r['sold'] }}</td>
                            <td class="text-center text-warning fw-medium">{{ $r['returns'] > 0 ? '+' . $r['returns'] : '0' }}</td>
                            <td class="text-center text-secondary">{{ $r['adjustments'] >= 0 ? '+' . $r['adjustments'] : $r['adjustments'] }}</td>
                            <td class="text-center fw-bold fs-6 {{ $r['current_stock'] <= 0 ? 'text-danger' : 'text-dark' }}">
                                {{ $r['current_stock'] }} {{ $r['product']->unit }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $r['product']->stock_badge_class }}">{{ $r['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                No products cataloged in inventory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
