@extends('layouts.app')

@section('title', 'Add New Product')
@section('page_title', 'Create Product')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Add New Product</h5>
            <p class="text-muted small mb-0">Define specifications, wholesale pricing, and initial opening stock</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
    </div>

    <div class="card card-custom p-4">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-secondary">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Classic Backpack, Travel Rucksack" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">SKU Code <span class="text-danger">*</span></label>
                    <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="e.g. BP-001" required>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select Category...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Size / Capacity</label>
                    <input type="text" name="size" class="form-control" value="{{ old('size') }}" placeholder="e.g. Medium, 30L, 15.6 Inch">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Color</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color') }}" placeholder="e.g. Navy Blue, Black, Olive">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Wholesale Price (₹) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">₹</span>
                        <input type="number" step="0.01" name="wholesale_price" class="form-control @error('wholesale_price') is-invalid @enderror" value="{{ old('wholesale_price') }}" placeholder="0.00" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Default price for distributors & bulk orders</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Selling Price / MRP (₹) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">₹</span>
                        <input type="number" step="0.01" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" placeholder="0.00" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Standard retail selling price</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Unit of Measure <span class="text-danger">*</span></label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', 'Pcs') }}" placeholder="Pcs, Set, Bundle" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Opening Stock (Qty)</label>
                    <input type="number" name="opening_stock" class="form-control" value="{{ old('opening_stock', 0) }}" min="0">
                    <small class="text-muted" style="font-size: 0.72rem;">Creates opening stock movement record</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Minimum Stock Alert Level <span class="text-danger">*</span></label>
                    <input type="number" name="min_stock_level" class="form-control" value="{{ old('min_stock_level', 10) }}" min="0" required>
                    <small class="text-muted" style="font-size: 0.72rem;">Triggers low stock badge on dashboard</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Catalog Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Product Description / Notes</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Fabric material, warranty, water resistance details, etc.">{{ old('description') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('products.index') }}" class="btn btn-light border btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm px-4">Save Product</button>
            </div>
        </form>
    </div>

</div>
@endsection
