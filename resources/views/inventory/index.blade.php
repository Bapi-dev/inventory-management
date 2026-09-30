@extends('layouts.app')

@section('title', 'Stock Management')
@section('page_title', 'Inventory & Stock Management')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Inventory & Stock On Hand</h5>
            <p class="text-muted small mb-0">Track real-time stock levels, monitor reorder alerts, and record stock movements</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-clock-history"></i>
                <span>Movement History</span>
            </a>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#stockActionModal">
                <i class="bi bi-plus-slash-minus"></i>
                <span>Adjust / Inward / Return</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('inventory.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Bag / SKU</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. Rucksack or RK-001" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Stock Status</label>
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (Healthy)</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (Alert)</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (Zero)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                @if(request()->hasAny(['search', 'category_id', 'stock_status']))
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Stock Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Min Level</th>
                        <th class="text-center">Stock Status</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                        <tr>
                            <td class="text-muted">{{ $products->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                <code class="small text-primary">{{ $product->sku }}</code>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fs-6 fw-bold {{ $product->current_stock <= 0 ? 'text-danger' : ($product->current_stock <= $product->min_stock_level ? 'text-warning' : 'text-dark') }}">
                                    {{ $product->current_stock }}
                                </span>
                                <span class="small text-muted">{{ $product->unit }}</span>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $product->min_stock_level }} {{ $product->unit }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $product->stock_badge_class }}">
                                    {{ $product->stock_status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="openAdjustModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->current_stock }})" title="Quick Adjust Stock">
                                    <i class="bi bi-sliders me-1"></i> Adjust
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-2 opacity-50"></i>
                                No stock items match the selected filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Stock Adjustment / Inward / Return Modal -->
<div class="modal fade" id="stockActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.adjust') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalTitle">Record Stock Movement</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="modalProductId" class="form-select" required>
                            <option value="">Choose product...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) — Current: {{ $p->current_stock }} {{ $p->unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Movement Type <span class="text-danger">*</span></label>
                        <select name="movement_type" id="modalMovementType" class="form-select" required onchange="toggleCustomerReturnFields()">
                            <option value="stock_in">Stock In (Production / Inward Purchase)</option>
                            <option value="adjustment">Stock Adjustment (Audit / Correction)</option>
                            <option value="return">Customer Sales Return</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="modalQuantity" class="form-control" placeholder="e.g. 50 or -5" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Use positive number for addition. For adjustment, negative reduces stock.</small>
                    </div>

                    <!-- Customer Return Fields (hidden by default) -->
                    <div id="customerReturnFields" class="p-3 bg-light rounded border mb-3 d-none">
                        <div class="fw-semibold text-primary small mb-2"><i class="bi bi-person-check-fill me-1"></i> Customer Credit Options</div>
                        <div class="mb-2">
                            <label class="form-label small">Customer</label>
                            <select name="customer_id" class="form-select form-select-sm">
                                <option value="">Select customer (optional)...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->customer_type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label small">Credit Amount to Customer (₹)</label>
                            <input type="number" step="0.01" name="credit_amount" class="form-control form-control-sm" placeholder="e.g. 5000.00">
                            <small class="text-muted" style="font-size: 0.7rem;">Credits the customer's financial ledger automatically</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason / Reference Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Production Batch #B402, Stock count audit, Return from customer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Movement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdjustModal(productId, productName, currentStock) {
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalTitle').innerText = 'Adjust Stock: ' + productName;
        const myModal = new bootstrap.Modal(document.getElementById('stockActionModal'));
        myModal.show();
    }

    function toggleCustomerReturnFields() {
        const type = document.getElementById('modalMovementType').value;
        const returnBox = document.getElementById('customerReturnFields');
        if (type === 'return') {
            returnBox.classList.remove('d-none');
        } else {
            returnBox.classList.add('d-none');
        }
    }
</script>
@endpush
