@extends('layouts.app')

@section('title', $customer->name)
@section('page_title', 'Customer Profile & Ledger')

@section('content')
<div class="container-fluid px-0">

    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold text-dark mb-0">{{ $customer->name }}</h4>
                <span class="badge bg-primary">{{ ucfirst($customer->customer_type) }}</span>
                <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($customer->status) }}</span>
            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-geo-alt me-1"></i>{{ $customer->address ? $customer->address . ', ' : '' }}{{ $customer->city ?? '' }}{{ $customer->state ? ', ' . $customer->state : '' }}
                • <i class="bi bi-telephone ms-2 me-1"></i>{{ $customer->mobile }}
                @if($customer->gstin) • <i class="bi bi-file-text ms-2 me-1"></i>GSTIN: <code>{{ $customer->gstin }}</code> @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cart-plus-fill"></i>
                <span>Create Order</span>
            </a>
            <a href="{{ route('payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cash-stack"></i>
                <span>Record Payment</span>
            </a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil"></i>
            </a>
        </div>
    </div>

    <!-- 4 Financial Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 metric-card" style="border-left-color: #3b82f6;">
                <span class="text-secondary small fw-medium">Total Billed Sales</span>
                <h4 class="fw-bold mb-0 text-dark">@currency($customer->total_sales)</h4>
                <small class="text-muted">Confirmed orders</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 metric-card" style="border-left-color: #10b981;">
                <span class="text-secondary small fw-medium">Total Payments Received</span>
                <h4 class="fw-bold mb-0 text-success">@currency($customer->total_paid)</h4>
                <small class="text-muted">Settled funds</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            @php $outstanding = $customer->outstanding_balance; @endphp
            <div class="card card-custom p-3 metric-card" style="border-left-color: #ef4444;">
                <span class="text-secondary small fw-medium">Current Outstanding</span>
                <h4 class="fw-bold mb-0 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                    @currency($outstanding)
                </h4>
                <small class="text-muted">Customer owes us</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 metric-card" style="border-left-color: #6366f1;">
                <span class="text-secondary small fw-medium">Credit Limit / Available</span>
                <h5 class="fw-bold mb-0 text-dark">@currency($customer->credit_limit)</h5>
                <small class="text-muted">Available: <span class="fw-semibold text-primary">@currency($customer->available_credit)</span></small>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs for Detailed History -->
    <div class="card card-custom mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs border-0 px-3 pt-2" id="customerTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger-pane" type="button" role="tab">
                        <i class="bi bi-journal-text me-1 text-primary"></i> Customer Ledger (Audit Trail)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-pane" type="button" role="tab">
                        <i class="bi bi-receipt me-1 text-primary"></i> Orders History ({{ $customer->orders->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-pane" type="button" role="tab">
                        <i class="bi bi-credit-card me-1 text-primary"></i> Payment Receipts ({{ $customer->payments->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-0" id="customerTabContent">

            <!-- 1. Chronological Double-Sided Ledger -->
            <div class="tab-pane fade show active" id="ledger-pane" role="tabpanel">
                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Double-Sided Ledger:</strong> Debit increases what customer owes (Orders); Credit decreases what customer owes (Payments / Returns).
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Ledger
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 130px;">Date</th>
                                <th>Transaction Description</th>
                                <th>Reference</th>
                                <th class="text-end" style="width: 140px;">Debit (+)</th>
                                <th class="text-end" style="width: 140px;">Credit (-)</th>
                                <th class="text-end" style="width: 160px;">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->ledgerEntries as $entry)
                                <tr>
                                    <td class="text-nowrap text-secondary small">{{ $entry->transaction_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $entry->description }}</span>
                                    </td>
                                    <td class="small">
                                        @if($entry->reference_type === 'order' && $entry->reference_id)
                                            <a href="{{ route('orders.show', $entry->reference_id) }}" class="text-primary text-decoration-none">
                                                Order #{{ $entry->reference_id }}
                                            </a>
                                        @elseif($entry->reference_type === 'payment' && $entry->reference_id)
                                            <a href="{{ route('payments.receipt', $entry->reference_id) }}" class="text-success text-decoration-none">
                                                Receipt #{{ $entry->reference_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $entry->reference_type)) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-danger">
                                        {{ $entry->debit > 0 ? '₹' . number_format($entry->debit, 2) : '—' }}
                                    </td>
                                    <td class="text-end fw-semibold text-success">
                                        {{ $entry->credit > 0 ? '₹' . number_format($entry->credit, 2) : '—' }}
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        @currency($entry->running_balance)
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                        No ledger transactions recorded yet for this customer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($customer->ledgerEntries->count() > 0)
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Current Outstanding Balance:</td>
                                    <td class="text-end text-danger">@currency($customer->ledgerEntries->sum('debit'))</td>
                                    <td class="text-end text-success">@currency($customer->ledgerEntries->sum('credit'))</td>
                                    <td class="text-end fs-6 text-primary">@currency($customer->outstanding_balance)</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- 2. Recent Orders Tab -->
            <div class="tab-pane fade" id="orders-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="fw-semibold text-primary text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="text-nowrap text-secondary small">{{ $order->order_date->format('d M Y') }}</td>
                                    <td class="text-end fw-semibold">@currency($order->grand_total)</td>
                                    <td class="text-end text-success">@currency($order->paid_amount)</td>
                                    <td class="text-end fw-semibold {{ $order->outstanding_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                        @currency($order->outstanding_amount)
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->payment_badge_class }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->status_badge_class }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        No orders placed by this customer yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Payments Tab -->
            <div class="tab-pane fade" id="payments-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Receipt / Payment #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference #</th>
                                <th>Linked Order</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->payments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('payments.receipt', $payment) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $payment->payment_number }}
                                        </a>
                                    </td>
                                    <td class="text-nowrap text-secondary small">{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $payment->method_label }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $payment->reference_number ?? '—' }}</td>
                                    <td class="small">
                                        @if($payment->order)
                                            <a href="{{ route('orders.show', $payment->order) }}" class="text-primary text-decoration-none">
                                                {{ $payment->order->order_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">Direct on Account</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">@currency($payment->amount)</td>
                                    <td class="text-end">
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        No payments recorded from this customer yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
