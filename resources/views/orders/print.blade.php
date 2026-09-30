@extends('layouts.print')

@section('title', 'Invoice ' . $order->order_number)

@section('content')
<div class="invoice-container">
    <!-- Invoice Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="bg-primary text-white p-2 rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-handbag-fill fs-5"></i>
                </span>
                <h3 class="fw-bold mb-0 text-dark">{{ $settings['business_name'] ?? 'BagSmart Wholesale Manufacturing' }}</h3>
            </div>
            <div class="text-muted small">
                {{ $settings['business_address'] ?? 'Industrial Bag Manufacturing Zone, Sector 4, Mumbai, Maharashtra 400001' }}<br>
                Phone: {{ $settings['business_mobile'] ?? '+91 98200 12345' }} • Email: {{ $settings['business_email'] ?? 'sales@bagsmart.com' }}<br>
                GSTIN: <strong>{{ $settings['business_gstin'] ?? '27ABCDE1234F1Z5' }}</strong>
            </div>
        </div>

        <div class="text-end">
            <h4 class="fw-bold text-primary mb-1">TAX INVOICE</h4>
            <div class="fs-6 fw-semibold text-dark">{{ $order->order_number }}</div>
            <div class="text-muted small">Date: {{ $order->order_date->format('d M Y') }}</div>
            <div class="mt-2">
                <span class="badge border text-dark">{{ ucfirst($order->status) }}</span>
                <span class="badge border {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-light text-dark' }}">
                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Bill To Details -->
    <div class="row mb-4">
        <div class="col-7">
            <h6 class="text-uppercase text-secondary fw-bold small mb-2">Billed To (Customer):</h6>
            <div class="fw-bold text-dark fs-6">{{ $order->customer->name }}</div>
            <div class="text-muted small">
                {{ $order->customer->address ?? 'Address not specified' }}<br>
                {{ $order->customer->city }}{{ $order->customer->state ? ', ' . $order->customer->state : '' }} {{ $order->customer->pincode }}<br>
                Contact: {{ $order->customer->mobile }} @if($order->customer->email) • {{ $order->customer->email }} @endif<br>
                @if($order->customer->gstin)
                    GSTIN: <strong>{{ $order->customer->gstin }}</strong>
                @endif
            </div>
        </div>
        <div class="col-5 text-end">
            <h6 class="text-uppercase text-secondary fw-bold small mb-2">Dispatch Details:</h6>
            <div class="small text-muted">
                Order Type: <strong>Wholesale Booking</strong><br>
                Payment Terms: <strong>Net 30 Days</strong><br>
                @if($order->notes)
                    Instructions: <em>{{ $order->notes }}</em>
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Product & SKU</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 110px;">Unit Price</th>
                    <th class="text-end" style="width: 90px;">Discount</th>
                    <th class="text-end" style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td class="text-muted text-center">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->product->name }}</div>
                            <small class="text-muted">SKU: {{ $item->product->sku }} | Category: {{ $item->product->category->name }}</small>
                        </td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end">@currency($item->unit_price)</td>
                        <td class="text-end text-muted">{{ $item->discount > 0 ? '₹' . number_format($item->discount, 2) : '—' }}</td>
                        <td class="text-end fw-bold">@currency($item->subtotal)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary & Totals -->
    <div class="row justify-content-end mb-4">
        <div class="col-6">
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="text-muted">Subtotal:</td>
                    <td class="text-end fw-semibold">@currency($order->subtotal)</td>
                </tr>
                @if($order->discount > 0)
                    <tr>
                        <td class="text-muted">Order Discount:</td>
                        <td class="text-end text-danger">- @currency($order->discount)</td>
                    </tr>
                @endif
                @if($order->tax > 0)
                    <tr>
                        <td class="text-muted">Tax / GST:</td>
                        <td class="text-end text-muted">+ @currency($order->tax)</td>
                    </tr>
                @endif
                <tr class="border-top">
                    <td class="fw-bold fs-6">Grand Total:</td>
                    <td class="text-end fw-bold fs-6 text-primary">@currency($order->grand_total)</td>
                </tr>
                <tr>
                    <td class="text-muted">Amount Paid:</td>
                    <td class="text-end fw-semibold text-success">@currency($order->paid_amount)</td>
                </tr>
                <tr class="border-top border-dark">
                    <td class="fw-bold text-danger">Balance Due:</td>
                    <td class="text-end fw-bold text-danger fs-6">@currency($order->outstanding_amount)</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Terms & Signature -->
    <div class="row pt-4 border-top mt-4">
        <div class="col-8">
            <h6 class="small fw-bold text-secondary text-uppercase mb-1">Terms & Conditions:</h6>
            <div class="small text-muted" style="font-size: 0.75rem;">
                {{ $settings['invoice_terms'] ?? '1. Goods once sold will not be returned unless manufacturing defect is notified within 7 days. 2. Interest @ 18% p.a. will be charged on overdue payments. 3. Subject to local jurisdiction.' }}
            </div>
        </div>
        <div class="col-4 text-center mt-3">
            <div style="height: 45px;"></div>
            <div class="border-top border-dark small fw-semibold pt-1">Authorized Signatory</div>
            <div class="small text-muted" style="font-size: 0.72rem;">{{ $settings['business_name'] ?? 'BagSmart Wholesale' }}</div>
        </div>
    </div>
</div>
@endsection
