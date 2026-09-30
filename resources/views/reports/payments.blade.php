@extends('layouts.app')

@section('title', 'Payment Report')
@section('page_title', 'Customer Collections Report')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Customer Payment Report</h5>
            <p class="text-muted small mb-0">Collections summary by date, customer, and settlement instrument</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i> Print View
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-3 mb-4 d-print-none">
        <form method="GET" action="{{ route('reports.payments') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Payment Method</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    <option value="cash" {{ $method === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ $method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="upi" {{ $method === 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="cheque" {{ $method === 'cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Generate</button>
                <a href="{{ route('reports.payments') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Metric Card -->
    <div class="card card-custom p-3 mb-4 bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-secondary small fw-medium">Total Collections in Period</span>
                <h3 class="fw-bold mb-0 text-success">@currency($totalPayments)</h3>
            </div>
            <div class="badge bg-success bg-opacity-10 text-success p-2 fs-6">
                {{ $payments->count() }} Payments Recorded
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Order Ref</th>
                        <th>Method</th>
                        <th>Reference #</th>
                        <th class="text-end">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>
                                <a href="{{ route('payments.receipt', $p) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $p->payment_number }}
                                </a>
                            </td>
                            <td class="text-nowrap text-secondary small">{{ $p->payment_date->format('d M Y') }}</td>
                            <td>{{ $p->customer->name }}</td>
                            <td class="small text-muted">{{ $p->order ? $p->order->order_number : 'Direct on Account' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->method_label }}</span></td>
                            <td class="small text-muted">{{ $p->reference_number ?? '—' }}</td>
                            <td class="text-end fw-bold text-success">@currency($p->amount)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No payments found for the selected dates.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
