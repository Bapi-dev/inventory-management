@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)
@section('page_title', 'Order Details')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold text-dark mb-0">{{ $order->order_number }}</h4>
                <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                <span class="badge {{ $order->payment_badge_class }}">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</span>
            </div>
            <p class="text-muted small mb-0">
                Booked on {{ $order->order_date->format('d F Y') }} • Customer:
                <a href="{{ route('customers.show', $order->customer) }}" class="fw-semibold text-primary text-decoration-none">
                    {{ $order->customer->name }}
                </a> ({{ ucfirst($order->customer->customer_type) }})
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Contextual Status Progression Buttons -->
            @if($order->status === 'draft')
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="btn btn-success btn-sm shadow-sm" onclick="return confirm('Confirm this order? Inventory will decrease and customer ledger will be debited.')">
                        <i class="bi bi-check-circle-fill me-1"></i> Confirm Order
                    </button>
                </form>
            @elseif($order->status === 'confirmed')
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="packed">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                        <i class="bi bi-box-seam me-1"></i> Mark as Packed
                    </button>
                </form>
            @elseif($order->status === 'packed')
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="dispatched">
                    <button type="submit" class="btn btn-warning btn-sm text-dark shadow-sm">
                        <i class="bi bi-truck me-1"></i> Mark as Dispatched
                    </button>
                </form>
            @elseif($order->status === 'dispatched')
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="btn btn-success btn-sm shadow-sm">
                        <i class="bi bi-check2-all me-1"></i> Mark as Delivered
                    </button>
                </form>
            @endif

            <!-- Cancel Button if not yet dispatched or delivered or cancelled -->
            @if(in_array($order->status, ['draft', 'confirmed', 'packed']))
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this order? Any deducted stock will be restored to inventory.')">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                </form>
            @endif

            <!-- Record Payment Button if outstanding > 0 -->
            @if($order->outstanding_amount > 0 && $order->status !== 'cancelled')
                <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="bi bi-cash-coin me-1"></i> Record Payment
                </button>
            @endif

            <a href="{{ route('orders.print', $order) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </a>
        </div>
    </div>

    <!-- Main Order Details Grid -->
    <div class="row g-4">
        <!-- Order Items & Customer Info (8 cols) -->
        <div class="col-lg-8">
            <!-- Items Card -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Ordered Items & Quantities</h6>
                    <span class="badge bg-light text-muted border">{{ $order->items->count() }} Line Items</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $idx => $item)
                                <tr>
                                    <td class="text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->product->name }}</div>
                                        <code class="small text-primary">{{ $item->product->sku }}</code>
                                        <span class="text-muted small">• {{ $item->product->category->name }}</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->quantity }} {{ $item->product->unit }}</td>
                                    <td class="text-end">@currency($item->unit_price)</td>
                                    <td class="text-end text-muted">{{ $item->discount > 0 ? '₹' . number_format($item->discount, 2) : '—' }}</td>
                                    <td class="text-end fw-bold text-dark">@currency($item->subtotal)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes & Dispatch Info -->
            @if($order->notes)
                <div class="card card-custom p-3 mb-4">
                    <h6 class="fw-bold text-secondary small mb-1">Order Notes & Transport</h6>
                    <p class="mb-0 text-dark">{{ $order->notes }}</p>
                </div>
            @endif

            <!-- Payments Recorded Against this Order -->
            <div class="card card-custom">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-credit-card me-1 text-success"></i> Payments Allocated to this Order</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference #</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->payments as $pay)
                                <tr>
                                    <td>
                                        <a href="{{ route('payments.receipt', $pay) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $pay->payment_number }}
                                        </a>
                                    </td>
                                    <td class="text-nowrap text-secondary small">{{ $pay->payment_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $pay->method_label }}</span></td>
                                    <td class="small text-muted">{{ $pay->reference_number ?? '—' }}</td>
                                    <td class="text-end fw-bold text-success">@currency($pay->amount)</td>
                                    <td class="text-end">
                                        <a href="{{ route('payments.receipt', $pay) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        No payments recorded directly against this order yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Summary & Customer Card (4 cols) -->
        <div class="col-lg-4">
            <!-- Financial Breakdown Card -->
            <div class="card card-custom p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Financial Breakdown</h6>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Items Subtotal:</span>
                    <span class="fw-semibold text-dark">@currency($order->subtotal)</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Discount:</span>
                    <span class="text-muted">{{ $order->discount > 0 ? '- ₹' . number_format($order->discount, 2) : '₹0.00' }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax / GST:</span>
                    <span class="text-muted">{{ $order->tax > 0 ? '+ ₹' . number_format($order->tax, 2) : '₹0.00' }}</span>
                </div>

                <div class="p-3 bg-light rounded border my-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Grand Total:</span>
                        <span class="fw-bold text-primary fs-5">@currency($order->grand_total)</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Paid:</span>
                    <span class="fw-bold text-success">@currency($order->paid_amount)</span>
                </div>

                <div class="d-flex justify-content-between pt-2 border-top">
                    <span class="fw-bold text-dark">Order Outstanding:</span>
                    <span class="fw-bold fs-6 {{ $order->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                        @currency($order->outstanding_amount)
                    </span>
                </div>
            </div>

            <!-- Customer Details Summary -->
            <div class="card card-custom p-4">
                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Customer Profile</h6>
                <div class="fw-bold fs-6 text-dark">{{ $order->customer->name }}</div>
                <div class="text-muted small mb-2">{{ ucfirst($order->customer->customer_type) }}</div>

                <div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i> {{ $order->customer->mobile }}</div>
                @if($order->customer->email)
                    <div class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i> {{ $order->customer->email }}</div>
                @endif
                @if($order->customer->address)
                    <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1"></i> {{ $order->customer->address }}</div>
                @endif
                @if($order->customer->gstin)
                    <div class="small text-muted mb-3"><i class="bi bi-file-text me-1"></i> GSTIN: <code>{{ $order->customer->gstin }}</code></div>
                @endif

                <div class="pt-2 border-top">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Customer Total Outstanding:</span>
                        <span class="small fw-bold text-danger">@currency($order->customer->outstanding_balance)</span>
                    </div>
                    <a href="{{ route('customers.show', $order->customer) }}" class="btn btn-outline-primary btn-sm w-100 mt-2">
                        View Full Customer Ledger <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Quick Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $order->customer_id }}">
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="redirect_to_order" value="1">

                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Record Payment for Order #{{ $order->order_number }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Order Total:</span>
                            <span class="fw-semibold text-dark">@currency($order->grand_total)</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Already Paid:</span>
                            <span class="fw-semibold text-success">@currency($order->paid_amount)</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold text-dark pt-1 border-top">
                            <span>Remaining Due:</span>
                            <span class="text-danger">@currency($order->outstanding_amount)</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ $order->outstanding_amount }}" required>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Enter partial amount (e.g. ₹30,000) or full due</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="upi">UPI (GPay / PhonePe)</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reference / UTR / Cheque Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. UTR#938210491 or Cheque #000124">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional payment remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
