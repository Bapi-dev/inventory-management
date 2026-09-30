@extends('layouts.app')

@section('title', 'Outstanding Receivables')
@section('page_title', 'Customer Outstanding Management')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Outstanding Receivables</h5>
            <p class="text-muted small mb-0">Monitor overdue balances, payment aging, and collect customer dues</p>
        </div>
        <div class="card card-custom py-2 px-3 border-danger">
            <div class="small text-muted">Total Filtered Outstanding:</div>
            <div class="fs-5 fw-bold text-danger">@currency($totalOutstandingSum)</div>
        </div>
    </div>

    <!-- Filter & Sort Card -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('outstanding.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Customer / Mobile</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. ABC Bags or 982..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Balance Filter</label>
                <select name="filter" class="form-select form-select-sm">
                    <option value="due" {{ $filter === 'due' ? 'selected' : '' }}>Outstanding Due (> ₹0)</option>
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Customers (Including ₹0)</option>
                    <option value="partially_paid" {{ $filter === 'partially_paid' ? 'selected' : '' }}>Partially Paid Orders</option>
                    <option value="overdue" {{ $filter === 'overdue' ? 'selected' : '' }}>Overdue (> 30 Days)</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Sort By</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="highest_outstanding" {{ $sort === 'highest_outstanding' ? 'selected' : '' }}>Highest Outstanding First</option>
                    <option value="oldest_due" {{ $sort === 'oldest_due' ? 'selected' : '' }}>Oldest Due (Longest Aging)</option>
                    <option value="customer_name" {{ $sort === 'customer_name' ? 'selected' : '' }}>Customer Name (A-Z)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
                @if(request()->hasAny(['search', 'filter', 'sort']))
                    <a href="{{ route('outstanding.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Outstanding Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th class="text-end">Total Billed</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Outstanding Balance</th>
                        <th>Last Payment</th>
                        <th class="text-center">Days Due (Aging)</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $idx => $r)
                        <tr>
                            <td class="text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <a href="{{ route('customers.show', $r['customer']) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $r['customer']->name }}
                                </a>
                                <div class="small text-muted">{{ $r['customer']->mobile }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border">{{ ucfirst($r['customer']->customer_type) }}</span>
                            </td>
                            <td class="text-end fw-semibold">@currency($r['total_sales'])</td>
                            <td class="text-end text-success fw-medium">@currency($r['total_paid'])</td>
                            <td class="text-end fw-bold {{ $r['outstanding'] > 0 ? 'text-danger fs-6' : 'text-success' }}">
                                @currency($r['outstanding'])
                            </td>
                            <td class="small text-muted">
                                @if($r['last_payment_amount'] > 0)
                                    <div>@currency($r['last_payment_amount'])</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $r['last_payment_date'] }}</div>
                                @else
                                    <span class="text-muted">No payments</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($r['outstanding'] > 0)
                                    <span class="badge {{ $r['days_outstanding'] >= 30 ? 'bg-danger' : ($r['days_outstanding'] >= 15 ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">
                                        {{ $r['days_outstanding'] }} days
                                    </span>
                                @else
                                    <span class="badge bg-success">Settled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $r['customer']) }}" class="btn btn-outline-primary" title="Customer Ledger">
                                        <i class="bi bi-journal-text me-1"></i> Ledger
                                    </a>
                                    @if($r['outstanding'] > 0)
                                        <a href="{{ route('payments.create', ['customer_id' => $r['customer']->id]) }}" class="btn btn-outline-success" title="Collect Payment">
                                            <i class="bi bi-cash"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                No customer outstanding balances matching this filter!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
