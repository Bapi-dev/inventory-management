<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected LedgerService $ledgerService
    ) {}

    /**
     * Generate customer-facing order number e.g. ORD-2026-0001
     */
    public function generateOrderNumber(): string
    {
        $year = date('Y');
        $latestOrder = Order::where('order_number', 'like', "ORD-{$year}-%")
            ->latest('id')
            ->first();

        if ($latestOrder) {
            $parts = explode('-', $latestOrder->order_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('ORD-%s-%04d', $year, $seq);
    }

    /**
     * Create an order with items atomically.
     *
     * @throws Exception
     */
    public function createOrder(array $data, array $items, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $customer = Customer::findOrFail($data['customer_id']);
            $orderNumber = $this->generateOrderNumber();

            // Calculate totals securely
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];
                $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
                $tax = isset($item['tax']) ? (float) $item['tax'] : 0;
                $lineSubtotal = round(($qty * $price) - $discount + $tax, 2);

                $subtotal += round(($qty * $price) - $discount, 2);

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount' => $discount,
                    'tax' => $tax,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $orderDiscount = isset($data['discount']) ? (float) $data['discount'] : 0;
            $orderTax = isset($data['tax']) ? (float) $data['tax'] : 0;
            $grandTotal = round(max(0, $subtotal - $orderDiscount + $orderTax), 2);

            $status = $data['status'] ?? 'draft';

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'status' => $status,
                'payment_status' => 'unpaid',
                'subtotal' => round($subtotal, 2),
                'discount' => round($orderDiscount, 2),
                'tax' => round($orderTax, 2),
                'grand_total' => $grandTotal,
                'paid_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            // If confirmed right away, deduct stock and debit ledger
            if (in_array($status, \App\Services\CustomerFinancialService::VALID_SALES_STATUSES)) {
                $this->inventoryService->deductOrderStock($order, $userId);
                $this->ledgerService->recordDebit(
                    customer: $customer,
                    amount: $order->grand_total,
                    description: "Order #{$order->order_number}",
                    referenceType: 'order',
                    referenceId: $order->id,
                    date: $order->order_date ? $order->order_date->format('Y-m-d') : null,
                    userId: $userId
                );
            }

            return $order;
        });
    }

    /**
     * Update order status with atomic stock & ledger reconciliation.
     *
     * @throws Exception
     */
    public function updateStatus(Order $order, string $newStatus, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $userId) {
            $oldStatus = $order->status;
            if ($oldStatus === $newStatus) {
                return $order;
            }

            $validSales = \App\Services\CustomerFinancialService::VALID_SALES_STATUSES;

            // Transition: Draft -> Active Confirmed/Packed/Dispatched/Delivered
            if ($oldStatus === 'draft' && in_array($newStatus, $validSales)) {
                $order->status = $newStatus;
                $order->save();

                $this->inventoryService->deductOrderStock($order, $userId);
                $this->ledgerService->recordDebit(
                    customer: $order->customer,
                    amount: $order->grand_total,
                    description: "Order #{$order->order_number}",
                    referenceType: 'order',
                    referenceId: $order->id,
                    date: $order->order_date ? $order->order_date->format('Y-m-d') : now()->toDateString(),
                    userId: $userId
                );
                return $order;
            }

            // Transition to Cancelled from an Active Sale
            if ($newStatus === 'cancelled') {
                if (in_array($oldStatus, $validSales)) {
                    // Restore stock
                    $this->inventoryService->restoreOrderStock($order, $userId);

                    // Reverse ledger debit with a credit entry
                    $this->ledgerService->recordCredit(
                        customer: $order->customer,
                        amount: $order->grand_total,
                        description: "Cancellation Reversal for Order #{$order->order_number}",
                        referenceType: 'cancellation',
                        referenceId: $order->id,
                        date: now()->toDateString(),
                        userId: $userId
                    );
                }
                $order->status = 'cancelled';
                $order->save();
                return $order;
            }

            // Standard status progression (e.g. Confirmed -> Packed -> Dispatched -> Delivered)
            $order->status = $newStatus;
            $order->save();

            return $order;
        });
    }
}
