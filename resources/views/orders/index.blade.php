@extends('layouts.app')

@section('title', 'Wholesale Orders')
@section('page_title', 'Wholesale Orders Management')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Wholesale Orders</h5>
            <p class="text-muted small mb-0">Track distributor bookings, dispatch workflows, and invoice payment statuses</p>
        </div>
        <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-cart-plus-fill"></i>
            <span>Create New Order</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Order # or Customer</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. ORD-2026 or Metro" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Order Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="packed" {{ request('status') === 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Payment Status</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All Payment</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                @if(request()->hasAny(['search', 'status', 'payment_status', 'from_date', 'to_date']))
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Outstanding</th>
                        <th class="text-center">Payment Status</th>
                        <th class="text-center">Order Status</th>
                        <th class="text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('orders.show', $order) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $order->customer) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $order->customer->name }}
                                </a>
                                <div class="text-muted small">{{ ucfirst($order->customer->customer_type) }}</div>
                            </td>
                            <td class="text-nowrap text-secondary small">{{ $order->order_date->format('d M Y') }}</td>
                            <td class="text-end fw-semibold">@currency($order->grand_total)</td>
                            <td class="text-end text-success fw-medium">@currency($order->paid_amount)</td>
                            <td class="text-end fw-bold {{ $order->outstanding_amount > 0 ? 'text-danger' : 'text-muted' }}">
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
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary" title="View Order">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('orders.print', $order) }}" target="_blank" class="btn btn-outline-secondary" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-50"></i>
                                No orders found matching filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
