@extends('layouts.app')

@section('title', 'New Wholesale Order')
@section('page_title', 'Create Wholesale Order')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">New Wholesale Order</h5>
            <p class="text-muted small mb-0">Select a wholesale customer, add bag line items with wholesale rates, and confirm order</p>
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Customer & Items -->
            <div class="col-lg-8">
                <!-- Customer Selection Card -->
                <div class="card card-custom p-4 mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-fill text-primary me-1"></i> Customer Information</h6>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold text-secondary">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror" required onchange="onCustomerChange()">
                                <option value="">Choose a wholesale customer...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        data-type="{{ $c->customer_type }}"
                                        data-outstanding="{{ $c->outstanding_balance }}"
                                        data-credit-limit="{{ $c->credit_limit }}"
                                        data-available-credit="{{ $c->available_credit }}"
                                        {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ ucfirst($c->customer_type) }}) — {{ $c->mobile }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <!-- Customer Financial Health Preview -->
                    <div id="customerFinancialBox" class="mt-3 p-3 bg-light rounded border d-none">
                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <span class="small text-muted d-block">Current Outstanding</span>
                                <span class="fw-bold fs-6 text-danger" id="custOutstanding">₹0.00</span>
                            </div>
                            <div class="col-4 border-end">
                                <span class="small text-muted d-block">Credit Limit</span>
                                <span class="fw-bold fs-6 text-dark" id="custCreditLimit">₹0.00</span>
                            </div>
                            <div class="col-4">
                                <span class="small text-muted d-block">Available Credit</span>
                                <span class="fw-bold fs-6 text-primary" id="custAvailableCredit">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table Card -->
                <div class="card card-custom p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-boxes text-primary me-1"></i> Order Items</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItemRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Product
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 200px;">Product & Stock</th>
                                    <th style="width: 100px;">Qty</th>
                                    <th style="width: 130px;">Unit Price (₹)</th>
                                    <th style="width: 100px;">Discount (₹)</th>
                                    <th style="width: 130px;" class="text-end">Subtotal (₹)</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Dynamic rows injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-light border btn-sm text-primary" onclick="addItemRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Item
                        </button>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card card-custom p-4">
                    <label class="form-label small fw-semibold text-secondary">Order Notes / Transport Instructions</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Transport via VRL Logistics, packed in heavy cartons">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Right Column: Summary & Confirmation -->
            <div class="col-lg-4">
                <div class="card card-custom p-4 sticky-top" style="top: 20px;">
                    <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Order Calculation</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Items Subtotal:</span>
                        <span class="fw-semibold text-dark" id="summarySubtotal">₹0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Order Discount (₹):</label>
                        <input type="number" step="0.01" min="0" name="discount" id="orderDiscountInput" class="form-control form-control-sm text-end" value="{{ old('discount', 0) }}" oninput="calculateTotals()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Tax / GST (₹):</label>
                        <input type="number" step="0.01" min="0" name="tax" id="orderTaxInput" class="form-control form-control-sm text-end" value="{{ old('tax', 0) }}" oninput="calculateTotals()">
                    </div>

                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">Grand Total:</span>
                            <span class="fw-bold text-primary fs-5" id="summaryGrandTotal">₹0.00</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Initial Order Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                            <option value="confirmed" selected>Confirmed (Deduct Stock & Debit Ledger)</option>
                            <option value="draft">Draft (Quotation - Hold Stock)</option>
                        </select>
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                            <i class="bi bi-info-circle me-1"></i> Selecting <strong>Confirmed</strong> automatically decreases physical inventory and updates the customer's ledger!
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Place Wholesale Order
                    </button>
                    <a href="{{ route('orders.index') }}" class="btn btn-light border w-100 btn-sm">Cancel</a>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    // Product data catalog passed safely to JavaScript
    const productsCatalog = {
        @foreach($products as $p)
            {{ $p->id }}: {
                id: {{ $p->id }},
                name: "{{ addslashes($p->name) }}",
                sku: "{{ $p->sku }}",
                wholesale_price: {{ $p->wholesale_price }},
                selling_price: {{ $p->selling_price }},
                current_stock: {{ $p->current_stock }},
                unit: "{{ $p->unit }}"
            },
        @endforeach
    };

    let rowIndex = 0;

    function onCustomerChange() {
        const select = document.getElementById('customerSelect');
        const box = document.getElementById('customerFinancialBox');
        if (!select.value) {
            box.classList.add('d-none');
            return;
        }

        const opt = select.options[select.selectedIndex];
        const out = parseFloat(opt.dataset.outstanding) || 0;
        const limit = parseFloat(opt.dataset.creditLimit) || 0;
        const avail = parseFloat(opt.dataset.availableCredit) || 0;

        document.getElementById('custOutstanding').innerText = '₹' + out.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('custCreditLimit').innerText = '₹' + limit.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('custAvailableCredit').innerText = '₹' + avail.toLocaleString(undefined, {minimumFractionDigits: 2});
        box.classList.remove('d-none');

        // Update default unit prices for existing rows based on customer type
        updateAllRowPrices(opt.dataset.type);
    }

    function updateAllRowPrices(customerType) {
        document.querySelectorAll('.item-row').forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const priceInput = row.querySelector('.unit-price-input');
            const pId = productSelect.value;
            if (pId && productsCatalog[pId]) {
                const prod = productsCatalog[pId];
                // For wholesale, distributor, retailer: wholesale_price; for individual: selling_price
                const defaultPrice = (customerType === 'individual') ? prod.selling_price : prod.wholesale_price;
                if (!priceInput.dataset.userEdited) {
                    priceInput.value = defaultPrice.toFixed(2);
                }
            }
            calculateRowSubtotal(row);
        });
        calculateTotals();
    }

    function addItemRow(selectedProdId = null, initialQty = 1) {
        const tbody = document.getElementById('itemsBody');
        const rId = rowIndex++;

        let productOptions = '<option value="">Select Bag Model...</option>';
        for (const [id, prod] of Object.entries(productsCatalog)) {
            const isSelected = (selectedProdId && id == selectedProdId) ? 'selected' : '';
            productOptions += `<option value="${prod.id}" ${isSelected}>${prod.name} (${prod.sku}) — Stock: ${prod.current_stock} ${prod.unit}</option>`;
        }

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.id = `row-${rId}`;
        tr.innerHTML = `
            <td>
                <select name="items[${rId}][product_id]" class="form-select form-select-sm product-select" required onchange="onProductSelect(this, ${rId})">
                    ${productOptions}
                </select>
                <div class="small text-muted mt-1 stock-preview" style="font-size: 0.72rem;">Available Stock: <span class="stock-badge fw-semibold">—</span></div>
            </td>
            <td>
                <input type="number" name="items[${rId}][quantity]" class="form-control form-control-sm qty-input" value="${initialQty}" min="1" required oninput="calculateRowSubtotal(document.getElementById('row-${rId}')); calculateTotals();">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" min="0" name="items[${rId}][unit_price]" class="form-control unit-price-input" placeholder="0.00" required oninput="this.dataset.userEdited=true; calculateRowSubtotal(document.getElementById('row-${rId}')); calculateTotals();">
                </div>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rId}][discount]" class="form-control form-control-sm discount-input" value="0" oninput="calculateRowSubtotal(document.getElementById('row-${rId}')); calculateTotals();">
            </td>
            <td class="text-end fw-bold text-dark row-subtotal">
                ₹0.00
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger p-0 border-0" onclick="removeItemRow(${rId})" title="Remove item">
                    <i class="bi bi-trash-fill fs-6"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        if (selectedProdId) {
            onProductSelect(tr.querySelector('.product-select'), rId);
        }
    }

    function removeItemRow(rId) {
        const row = document.getElementById(`row-${rId}`);
        if (row) {
            row.remove();
            calculateTotals();
        }
    }

    function onProductSelect(selectElement, rId) {
        const row = document.getElementById(`row-${rId}`);
        const pId = selectElement.value;
        const priceInput = row.querySelector('.unit-price-input');
        const stockBadge = row.querySelector('.stock-badge');

        if (!pId || !productsCatalog[pId]) {
            stockBadge.innerText = '—';
            priceInput.value = '';
            calculateRowSubtotal(row);
            calculateTotals();
            return;
        }

        const prod = productsCatalog[pId];
        stockBadge.innerText = `${prod.current_stock} ${prod.unit}`;
        if (prod.current_stock <= 0) {
            stockBadge.className = 'stock-badge fw-bold text-danger';
        } else {
            stockBadge.className = 'stock-badge fw-bold text-success';
        }

        // Determine wholesale vs selling based on customer
        const custSelect = document.getElementById('customerSelect');
        let price = prod.wholesale_price;
        if (custSelect.value) {
            const custType = custSelect.options[custSelect.selectedIndex].dataset.type;
            if (custType === 'individual') {
                price = prod.selling_price;
            }
        }

        priceInput.value = parseFloat(price).toFixed(2);
        calculateRowSubtotal(row);
        calculateTotals();
    }

    function calculateRowSubtotal(row) {
        if (!row) return;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

        const subtotal = Math.max(0, (qty * price) - discount);
        row.querySelector('.row-subtotal').innerText = '₹' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        row.dataset.calculatedSubtotal = subtotal;
    }

    function calculateTotals() {
        let sumSubtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const sub = parseFloat(row.dataset.calculatedSubtotal) || 0;
            sumSubtotal += sub;
        });

        const orderDiscount = parseFloat(document.getElementById('orderDiscountInput').value) || 0;
        const orderTax = parseFloat(document.getElementById('orderTaxInput').value) || 0;

        const grandTotal = Math.max(0, sumSubtotal - orderDiscount + orderTax);

        document.getElementById('summarySubtotal').innerText = '₹' + sumSubtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryGrandTotal').innerText = '₹' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Initialize with 1 empty row and trigger customer details if preselected
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('customerSelect').value) {
            onCustomerChange();
        }
        addItemRow();
    });
</script>
@endpush
