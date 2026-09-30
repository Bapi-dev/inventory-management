@extends('layouts.print')

@section('title', 'Receipt ' . $payment->payment_number)

@section('content')
<div class="receipt-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="bg-primary text-white p-2 rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-handbag-fill fs-5"></i>
                </span>
                <h4 class="fw-bold mb-0 text-dark">{{ $settings['business_name'] ?? 'BagSmart Wholesale Manufacturing' }}</h4>
            </div>
            <div class="text-muted small">
                {{ $settings['business_address'] ?? 'Industrial Bag Manufacturing Zone, Sector 4, Mumbai, Maharashtra 400001' }}<br>
                Phone: {{ $settings['business_mobile'] ?? '+91 98200 12345' }} • GSTIN: <strong>{{ $settings['business_gstin'] ?? '27ABCDE1234F1Z5' }}</strong>
            </div>
        </div>

        <div class="text-end">
            <h5 class="fw-bold text-success mb-1">PAYMENT RECEIPT</h5>
            <div class="fs-6 fw-semibold text-dark">{{ $payment->payment_number }}</div>
            <div class="text-muted small">Date: {{ $payment->payment_date->format('d M Y') }}</div>
        </div>
    </div>

    <!-- Customer & Payment Details -->
    <div class="row mb-4">
        <div class="col-7">
            <h6 class="text-uppercase text-secondary fw-bold small mb-2">Received From (Customer):</h6>
            <div class="fw-bold text-dark fs-6">{{ $payment->customer->name }}</div>
            <div class="text-muted small">
                {{ $payment->customer->address ?? '' }}<br>
                {{ $payment->customer->city }}{{ $payment->customer->state ? ', ' . $payment->customer->state : '' }}<br>
                Mobile: {{ $payment->customer->mobile }}
                @if($payment->customer->gstin) • GSTIN: {{ $payment->customer->gstin }} @endif
            </div>
        </div>

        <div class="col-5">
            <h6 class="text-uppercase text-secondary fw-bold small mb-2">Transaction Details:</h6>
            <div class="small">
                <strong>Method:</strong> {{ $payment->method_label }}<br>
                <strong>Reference / UTR:</strong> {{ $payment->reference_number ?? 'N/A' }}<br>
                @if($payment->order)
                    <strong>Against Order:</strong> {{ $payment->order->order_number }}<br>
                @endif
                <strong>Recorded By:</strong> {{ $payment->creator->name ?? 'Accounts Desk' }}
            </div>
        </div>
    </div>

    <!-- Amount & Outstanding Box -->
    <div class="card bg-light border p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-6">
                <span class="text-muted small text-uppercase fw-semibold">Amount Received:</span>
                <h3 class="fw-bold text-success mb-0">@currency($payment->amount)</h3>
            </div>
            <div class="col-6 text-end">
                <span class="badge bg-success px-3 py-2 fs-6">Payment Confirmed</span>
            </div>
        </div>
    </div>

    <!-- Outstanding Ledger Reconciliation -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ledger Particulars</th>
                    <th class="text-end" style="width: 200px;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-muted">Previous Customer Balance / Due:</td>
                    <td class="text-end fw-semibold">@currency($previousOutstanding)</td>
                </tr>
                <tr>
                    <td class="text-success fw-semibold">Less: This Payment (Credit):</td>
                    <td class="text-end fw-bold text-success">- @currency($payment->amount)</td>
                </tr>
                <tr class="table-light">
                    <td class="fw-bold text-dark">Remaining Customer Outstanding:</td>
                    <td class="text-end fw-bold fs-6 {{ $currentOutstanding > 0 ? 'text-danger' : 'text-success' }}">
                        @currency($currentOutstanding)
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($payment->notes)
        <div class="p-3 bg-white border rounded mb-4 small text-muted">
            <strong>Remarks:</strong> {{ $payment->notes }}
        </div>
    @endif

    <!-- Signature -->
    <div class="row pt-5 mt-5">
        <div class="col-6 text-center">
            <div style="height: 35px;"></div>
            <div class="border-top border-secondary small pt-1 text-muted">Customer Signature / Receiver</div>
        </div>
        <div class="col-6 text-center">
            <div style="height: 35px;"></div>
            <div class="border-top border-dark small fw-semibold pt-1">Authorized Signatory</div>
            <div class="small text-muted" style="font-size: 0.75rem;">{{ $settings['business_name'] ?? 'BagSmart Wholesale' }}</div>
        </div>
    </div>
</div>
@endsection
