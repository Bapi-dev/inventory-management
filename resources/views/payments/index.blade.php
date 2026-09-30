@extends('layouts.app')

@section('title', 'Payments')
@section('page_title', 'Customer Payments')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Payments Received</h5>
            <p class="text-muted small mb-0">Record collections from distributors, update ledger credit, and print receipts</p>
        </div>
        <a href="{{ route('payments.create') }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-cash-stack"></i>
            <span>Record New Payment</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Payment # / Ref #</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. PAY-2026 or UTR" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Payment Method</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="upi" {{ request('payment_method') === 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="other" {{ request('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                @if(request()->hasAny(['search', 'customer_id', 'payment_method', 'from_date', 'to_date']))
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Receipt / Payment #</th>
                        <th>Payment Date</th>
                        <th>Customer</th>
                        <th>Linked Order</th>
                        <th>Method</th>
                        <th>Reference #</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>
                                <a href="{{ route('payments.receipt', $p) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $p->payment_number }}
                                </a>
                            </td>
                            <td class="text-nowrap text-secondary small">{{ $p->payment_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('customers.show', $p->customer) }}" class="fw-semibold text-primary text-decoration-none">
                                    {{ $p->customer->name }}
                                </a>
                                <div class="text-muted small">{{ ucfirst($p->customer->customer_type) }}</div>
                            </td>
                            <td class="small">
                                @if($p->order)
                                    <a href="{{ route('orders.show', $p->order) }}" class="text-secondary text-decoration-none">
                                        {{ $p->order->order_number }}
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border">Direct on Account</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $p->method_label }}</span>
                            </td>
                            <td class="small text-muted">{{ $p->reference_number ?? '—' }}</td>
                            <td class="text-end fw-bold text-success fs-6">
                                @currency($p->amount)
                            </td>
                            <td class="text-end">
                                <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Print Receipt">
                                    <i class="bi bi-printer me-1"></i> Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-credit-card fs-1 d-block mb-2 opacity-50"></i>
                                No payments found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
