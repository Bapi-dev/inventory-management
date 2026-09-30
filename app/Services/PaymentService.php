<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {}

    /**
     * Generate customer-facing payment number e.g. PAY-2026-0001
     */
    public function generatePaymentNumber(): string
    {
        $year = date('Y');
        $latestPayment = Payment::where('payment_number', 'like', "PAY-{$year}-%")
            ->latest('id')
            ->first();

        if ($latestPayment) {
            $parts = explode('-', $latestPayment->payment_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('PAY-%s-%04d', $year, $seq);
    }

    /**
     * Record a payment atomically and update order status & customer ledger.
     *
     * @throws Exception
     */
    public function recordPayment(array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $customer = Customer::findOrFail($data['customer_id']);
            $order = !empty($data['order_id']) ? Order::findOrFail($data['order_id']) : null;
            $amount = round((float) $data['amount'], 2);

            if ($amount <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            // 1. Prevent duplicate submission with same reference number
            $ref = !empty($data['reference_number']) ? trim($data['reference_number']) : null;
            if ($ref) {
                $duplicateRef = Payment::where('customer_id', $customer->id)
                    ->where('reference_number', $ref)
                    ->exists();
                if ($duplicateRef) {
                    throw new Exception("A payment with reference number '{$ref}' has already been recorded for this customer.");
                }
            }

            // 2. Prevent rapid duplicate submission (double-click within 5 seconds)
            $recentDuplicate = Payment::where('customer_id', $customer->id)
                ->where('amount', $amount)
                ->where('order_id', $order?->id)
                ->where('created_at', '>=', now()->subSeconds(5))
                ->exists();
            if ($recentDuplicate) {
                throw new Exception("Duplicate payment submission detected. Please do not submit twice.");
            }

            $paymentNumber = $this->generatePaymentNumber();

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $ref,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Update order paid amount and payment status if linked
            if ($order) {
                $totalPaidForOrder = round((float) $order->payments()->sum('amount'), 2);
                $order->paid_amount = $totalPaidForOrder;

                if ($totalPaidForOrder >= $order->grand_total) {
                    $order->payment_status = Order::PAYMENT_PAID;
                } elseif ($totalPaidForOrder > 0) {
                    $order->payment_status = Order::PAYMENT_PARTIALLY_PAID;
                } else {
                    $order->payment_status = Order::PAYMENT_UNPAID;
                }

                $order->save();
            }

            // Record double-sided ledger credit entry
            $desc = "Payment #{$payment->payment_number}" . ($order ? " (Order #{$order->order_number})" : "");
            $this->ledgerService->recordCredit(
                customer: $customer,
                amount: $amount,
                description: $desc,
                referenceType: 'payment',
                referenceId: $payment->id,
                date: $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                userId: $userId
            );

            return $payment;
        });
    }
}
