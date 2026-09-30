@extends('layouts.app')

@section('title', 'Reports')
@section('page_title', 'Business Reports & Analytics')

@section('content')
<div class="container-fluid px-0">

    <div class="mb-4">
        <h5 class="fw-bold text-dark mb-1">Reports & Financial Analytics</h5>
        <p class="text-muted small mb-0">Generate print-ready statements and CSV exports for sales, stock, payments, and receivables</p>
    </div>

    <div class="row g-4">
        <!-- 1. Sales Report -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded d-inline-block mb-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Sales Report</h5>
                    <p class="text-muted small mb-4">Itemized breakdown of wholesale sales, quantities dispatched, discounts, and revenue totals.</p>
                </div>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary btn-sm w-100">
                    Open Sales Report <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 2. Payment Report -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded d-inline-block mb-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Payment Report</h5>
                    <p class="text-muted small mb-4">Complete audit of payment collections grouped by date, payment mode (Cash, Bank, UPI), and customer.</p>
                </div>
                <a href="{{ route('reports.payments') }}" class="btn btn-outline-success btn-sm w-100">
                    Open Payment Report <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 3. Outstanding Report -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded d-inline-block mb-3">
                        <i class="bi bi-exclamation-octagon fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Outstanding Report</h5>
                    <p class="text-muted small mb-4">Comprehensive list of all distributors and customers with pending debit balances.</p>
                </div>
                <a href="{{ route('reports.outstanding') }}" class="btn btn-outline-danger btn-sm w-100">
                    Open Outstanding Report <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 4. Stock Report -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-info bg-opacity-10 text-info rounded d-inline-block mb-3">
                        <i class="bi bi-boxes fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Stock Report</h5>
                    <p class="text-muted small mb-4">Complete inventory reconciliation showing opening balances, stock in, sold units, returns, and on-hand stock.</p>
                </div>
                <a href="{{ route('reports.stock') }}" class="btn btn-outline-info text-dark btn-sm w-100">
                    Open Stock Report <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
