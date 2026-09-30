@extends('layouts.app')

@section('title', 'Outstanding Report')
@section('page_title', 'Customer Outstanding Report')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Customer Outstanding Balance Report</h5>
            <p class="text-muted small mb-0">List of all wholesale customers with pending receivables</p>
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

    <div class="card card-custom p-3 mb-4 bg-light border-danger">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-secondary small fw-medium">Total Uncollected Receivables</span>
                <h3 class="fw-bold mb-0 text-danger">@currency($totalOutstanding)</h3>
            </div>
            <div class="badge bg-danger bg-opacity-10 text-danger p-2 fs-6">
                {{ count($rows) }} Customers with Dues
            </div>
        </div>
    </div>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Customer Name</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th class="text-end">Total Billed</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Outstanding Balance</th>
                        <th class="text-end" style="width: 100px;">Actions</th>
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
                            </td>
                            <td><span class="badge bg-light text-primary border">{{ ucfirst($r['customer']->customer_type) }}</span></td>
                            <td class="small">{{ $r['customer']->mobile }}</td>
                            <td class="text-end fw-semibold">@currency($r['total_sales'])</td>
                            <td class="text-end text-success fw-medium">@currency($r['total_paid'])</td>
                            <td class="text-end fw-bold text-danger fs-6">@currency($r['outstanding'])</td>
                            <td class="text-end">
                                <a href="{{ route('customers.show', $r['customer']) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                    Ledger
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                No customer outstanding balances. All accounts are settled!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
