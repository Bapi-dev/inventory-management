@extends('layouts.app')

@section('title', 'Products Catalog')
@section('page_title', 'Products & Catalog')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Product Catalog</h5>
            <p class="text-muted small mb-0">Manage bag models, SKUs, wholesale pricing, and baseline stock</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i>
            <span>Add New Product</span>
        </a>
    </div>

    <!-- Search & Filter Card -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Product / SKU</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. Classic Backpack or BP-001" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-center gap-2">
                <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input" type="checkbox" name="low_stock" id="lowStockCheck" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold text-danger" for="lowStockCheck">Low Stock Only</label>
                </div>

                <button type="submit" class="btn btn-primary btn-sm mt-3 px-3">Filter</button>
                @if(request()->hasAny(['search', 'category_id', 'status', 'low_stock']))
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm mt-3" title="Reset Filters">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Product Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product & SKU</th>
                        <th>Category</th>
                        <th>Specs</th>
                        <th class="text-end">Wholesale Price</th>
                        <th class="text-end">Selling Price</th>
                        <th class="text-center">Stock Level</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                        <tr>
                            <td class="text-muted">{{ $products->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                <code class="small text-primary fw-medium">{{ $product->sku }}</code>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $product->size ?? 'Standard' }} @if($product->color) • {{ $product->color }} @endif
                            </td>
                            <td class="text-end fw-bold text-primary">
                                @currency($product->wholesale_price)
                            </td>
                            <td class="text-end text-muted">
                                @currency($product->selling_price)
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $product->stock_badge_class }}">
                                    {{ $product->current_stock }} {{ $product->unit }}
                                </span>
                                <div class="text-muted" style="font-size: 0.72rem;">Min: {{ $product->min_stock_level }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary" title="Edit Product">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" onclick="confirmAction('Are you sure you want to delete product {{ $product->name }}?', 'del-prod-{{ $product->id }}')" title="Delete Product">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="del-prod-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-boxes fs-1 d-block mb-2 opacity-50"></i>
                                No products found matching criteria.
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
@endsection
