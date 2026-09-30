@extends('layouts.app')

@section('title', 'Business Settings')
@section('page_title', 'Business Settings & Profile')

@section('content')
<div class="container-fluid px-0" style="max-width: 850px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Business Profile & Document Settings</h5>
            <p class="text-muted small mb-0">Configure your manufacturing business details printed on wholesale invoices & receipts</p>
        </div>
    </div>

    <div class="card card-custom p-4">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf

            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-building text-primary me-1"></i> Enterprise Identity</h6>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-secondary">Business / Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $settings['business_name'] ?? 'BagSmart Wholesale Manufacturing') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Currency Symbol <span class="text-danger">*</span></label>
                    <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₹') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Manufacturing Factory / Registered Address</label>
                <textarea name="business_address" class="form-control" rows="2">{{ old('business_address', $settings['business_address'] ?? 'Plot #42, Industrial Area, Sector 7, Andheri East, Mumbai, Maharashtra - 400093') }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Contact Mobile / Phone</label>
                    <input type="text" name="business_mobile" class="form-control" value="{{ old('business_mobile', $settings['business_mobile'] ?? '+91 98200 12345') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Business Email</label>
                    <input type="email" name="business_email" class="form-control" value="{{ old('business_email', $settings['business_email'] ?? 'sales@bagsmart.com') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">GSTIN / Tax Identification</label>
                    <input type="text" name="business_gstin" class="form-control" value="{{ old('business_gstin', $settings['business_gstin'] ?? '27ABCDE1234F1Z5') }}">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-text text-primary me-1"></i> Invoice & Receipt Terms</h6>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Default Printable Invoice Terms & Conditions</label>
                <textarea name="invoice_terms" class="form-control" rows="3">{{ old('invoice_terms', $settings['invoice_terms'] ?? '1. Goods once sold will not be returned unless manufacturing defect is reported within 7 days. 2. Interest @ 18% p.a. will be levied on delayed payments beyond 30 days. 3. Subject to Mumbai Jurisdiction.') }}</textarea>
            </div>

            <div class="d-flex justify-content-end border-top pt-3">
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="bi bi-check2 me-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
