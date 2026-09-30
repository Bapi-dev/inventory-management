@extends('layouts.app')

@section('title', 'Edit Customer')
@section('page_title', 'Edit Wholesale Customer')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Edit Customer: {{ $customer->name }}</h5>
            <p class="text-muted small mb-0">Update contact details, credit limits, and addresses</p>
        </div>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Details
        </a>
    </div>

    <div class="card card-custom p-4">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-secondary">Customer / Business Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Customer Type <span class="text-danger">*</span></label>
                    <select name="customer_type" class="form-select @error('customer_type') is-invalid @enderror" required>
                        <option value="distributor" {{ old('customer_type', $customer->customer_type) === 'distributor' ? 'selected' : '' }}>Distributor</option>
                        <option value="wholesaler" {{ old('customer_type', $customer->customer_type) === 'wholesaler' ? 'selected' : '' }}>Wholesaler</option>
                        <option value="retailer" {{ old('customer_type', $customer->customer_type) === 'retailer' ? 'selected' : '' }}>Retailer</option>
                        <option value="individual" {{ old('customer_type', $customer->customer_type) === 'individual' ? 'selected' : '' }}>Individual Customer</option>
                    </select>
                    @error('customer_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $customer->mobile) }}" required>
                    @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state', $customer->state) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $customer->pincode) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Billing & Delivery Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">GSTIN / Tax ID</label>
                    <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $customer->gstin) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Credit Limit (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">₹</span>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Account Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-secondary">Internal Notes</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes', $customer->notes) }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-light border btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm px-4">Update Profile</button>
            </div>
        </form>
    </div>

</div>
@endsection
