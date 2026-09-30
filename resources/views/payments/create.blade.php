@extends('layouts.app')

@section('title', 'Record Payment')
@section('page_title', 'Record Customer Payment')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Record Customer Payment</h5>
            <p class="text-muted small mb-0">Record payment received, credit customer ledger, and update order payment status</p>
        </div>
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Payments
        </a>
    </div>

    <div class="card card-custom p-4">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold text-secondary">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="paymentCustomerSelect" class="form-select @error('customer_id') is-invalid @enderror" required onchange="onCustomerSelectChange()">
                        <option value="">Select Customer...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}"
                                data-outstanding="{{ $c->outstanding_balance }}"
                                {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ ucfirst($c->customer_type) }}) — Outstanding: ₹{{ number_format($c->outstanding_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-secondary">Linked Wholesale Order (Optional)</label>
                    <select name="order_id" id="orderSelect" class="form-select">
                        <option value="">Direct on Account (No specific order)</option>
                        @if($order)
                            <option value="{{ $order->id }}" selected>
                                {{ $order->order_number }} (Due: ₹{{ number_format($order->outstanding_amount, 2) }})
                            </option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Customer Outstanding Banner -->
            <div id="paymentOutstandingBanner" class="p-3 bg-light rounded border mb-3 d-none">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Customer Current Total Outstanding:</span>
                    <span class="fw-bold fs-6 text-danger" id="currentOutText">₹0.00</span>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Amount Received (₹) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">₹</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $order ? $order->outstanding_amount : '') }}" placeholder="0.00" required>
                    </div>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer (NEFT / RTGS / IMPS)</option>
                        <option value="upi" {{ old('payment_method') === 'upi' ? 'selected' : '' }}>UPI (Google Pay / PhonePe / QR)</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque / Demand Draft</option>
                        <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Transaction Ref # / Cheque #</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="e.g. UTR#298319203 or Chq #000491">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Payment Remarks / Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Received via HDFC Bank, against Invoice ORD-2026-0001">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('payments.index') }}" class="btn btn-light border btn-sm">Cancel</a>
                <button type="submit" id="paymentSubmitBtn" class="btn btn-success btn-sm px-4 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save & Generate Receipt
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function onCustomerSelectChange() {
        const select = document.getElementById('paymentCustomerSelect');
        const banner = document.getElementById('paymentOutstandingBanner');
        const text = document.getElementById('currentOutText');

        if (!select.value) {
            banner.classList.add('d-none');
            return;
        }

        const opt = select.options[select.selectedIndex];
        const out = parseFloat(opt.dataset.outstanding) || 0;
        text.innerText = '₹' + out.toLocaleString(undefined, {minimumFractionDigits: 2});
        banner.classList.remove('d-none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('paymentCustomerSelect').value) {
            onCustomerSelectChange();
        }

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                const btn = document.getElementById('paymentSubmitBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
                }
            });
        }
    });
</script>
@endpush
