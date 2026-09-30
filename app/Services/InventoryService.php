<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Record a stock movement atomically and update the product stock cache.
     *
     * @throws Exception
     */
    public function recordMovement(
        Product $product,
        string $movementType,
        int $quantity, // Positive for addition, negative for deduction
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $date = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        return DB::transaction(function () use (
            $product,
            $movementType,
            $quantity,
            $referenceType,
            $referenceId,
            $date,
            $notes,
            $userId
        ) {
            // Lock the product row for update
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $newStock = $lockedProduct->current_stock + $quantity;

            if ($newStock < 0) {
                throw new Exception("Insufficient stock for product '{$lockedProduct->name}'. Available: {$lockedProduct->current_stock}, Requested: " . abs($quantity));
            }

            $movement = StockMovement::create([
                'product_id' => $lockedProduct->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date ? date('Y-m-d H:i:s', strtotime($date)) : now(),
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            $lockedProduct->current_stock = $newStock;
            $lockedProduct->save();

            return $movement;
        });
    }

    /**
     * Deduct stock when an order is confirmed.
     *
     * @throws Exception
     */
    public function deductOrderStock(Order $order, ?int $userId = null): void
    {
        // Check if stock has already been deducted for this order
        $alreadyDeducted = StockMovement::where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->where('movement_type', 'sale')
            ->exists();

        if ($alreadyDeducted) {
            return;
        }

        foreach ($order->items as $item) {
            $product = $item->product;
            $this->recordMovement(
                product: $product,
                movementType: 'sale',
                quantity: -1 * abs($item->quantity),
                referenceType: 'Order',
                referenceId: $order->id,
                date: $order->order_date ? $order->order_date->format('Y-m-d H:i:s') : now()->toDateTimeString(),
                notes: "Deduction for Order #{$order->order_number}",
                userId: $userId
            );
        }
    }

    /**
     * Restore stock when a confirmed order is cancelled.
     *
     * @throws Exception
     */
    public function restoreOrderStock(Order $order, ?int $userId = null): void
    {
        // Check if stock was deducted for this order
        $wasDeducted = StockMovement::where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->where('movement_type', 'sale')
            ->exists();

        // Check if already restored
        $alreadyRestored = StockMovement::where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->where('movement_type', 'adjustment')
            ->where('notes', 'like', '%Restoration for Cancelled Order%')
            ->exists();

        if ($wasDeducted && !$alreadyRestored) {
            foreach ($order->items as $item) {
                $product = $item->product;
                $this->recordMovement(
                    product: $product,
                    movementType: 'adjustment',
                    quantity: abs($item->quantity),
                    referenceType: 'Order',
                    referenceId: $order->id,
                    date: now()->toDateTimeString(),
                    notes: "Restoration for Cancelled Order #{$order->order_number}",
                    userId: $userId
                );
            }
        }
    }
}
