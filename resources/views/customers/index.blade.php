@extends('layouts.app')

@section('title', 'Customers')
@section('page_title', 'Wholesale Customers')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Wholesale Customer Directory</h5>
            <p class="text-muted small mb-0">Manage distributors, wholesalers, retail partners, and customer credit</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-person-plus-fill"></i>
            <span>Add New Customer</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Customer Name / Mobile / City</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. ABC Bags or 9876543210" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Customer Type</label>
                <select name="customer_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="distributor" {{ request('customer_type') === 'distributor' ? 'selected' : '' }}>Distributor</option>
                    <option value="wholesaler" {{ request('customer_type') === 'wholesaler' ? 'selected' : '' }}>Wholesaler</option>
                    <option value="retailer" {{ request('customer_type') === 'retailer' ? 'selected' : '' }}>Retailer</option>
                    <option value="individual" {{ request('customer_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                @if(request()->hasAny(['search', 'customer_type', 'status']))
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customer Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $index => $customer)
                        @php
                            $bal = $customer->outstanding_balance;
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $customers->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $customer->name }}
                                </a>
                                @if($customer->city)
                                    <div class="small text-muted">{{ $customer->city }}{{ $customer->state ? ', ' . $customer->state : '' }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border">
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                            </td>
                            <td>
                                <div><i class="bi bi-telephone text-muted me-1 small"></i>{{ $customer->mobile }}</div>
                                @if($customer->email)
                                    <div class="text-muted small">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">
                                @currency($customer->total_sales)
                            </td>
                            <td class="text-end text-success fw-semibold">
                                @currency($customer->total_paid)
                            </td>
                            <td class="text-end fw-bold {{ $bal > 0 ? 'text-danger' : 'text-success' }}">
                                @currency($bal)
                                @if($customer->credit_balance > 0)
                                    <div class="small fw-normal text-primary" style="font-size: 0.72rem;">(Advance: @currency($customer->credit_balance))</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-primary" title="View Ledger & Orders">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary" title="Edit Profile">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" onclick="confirmAction('Delete customer {{ $customer->name }}?', 'del-cust-{{ $customer->id }}')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="del-cust-{{ $customer->id }}" action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
