<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Record a debit entry (Customer owes us more).
     */
    public function recordDebit(
        Customer $customer,
        float $amount,
        string $description,
        string $referenceType,
        ?int $referenceId = null,
        ?string $date = null,
        ?int $userId = null
    ): LedgerEntry {
        return DB::transaction(function () use (
            $customer,
            $amount,
            $description,
            $referenceType,
            $referenceId,
            $date,
            $userId
        ) {
            $amount = round((float) $amount, 2);
            $latestEntry = LedgerEntry::where('customer_id', $customer->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($referenceType === 'opening_balance') {
                $prevBalance = 0;
            } else {
                $prevBalance = $latestEntry ? (float) $latestEntry->running_balance : (float) $customer->opening_balance;
            }
            $newBalance = round($prevBalance + $amount, 2);

            return LedgerEntry::create([
                'customer_id' => $customer->id,
                'transaction_date' => $date ? date('Y-m-d', strtotime($date)) : now()->toDateString(),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'debit' => $amount,
                'credit' => 0,
                'running_balance' => $newBalance,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Record a credit entry (Customer paid us or was credited).
     */
    public function recordCredit(
        Customer $customer,
        float $amount,
        string $description,
        string $referenceType,
        ?int $referenceId = null,
        ?string $date = null,
        ?int $userId = null
    ): LedgerEntry {
        return DB::transaction(function () use (
            $customer,
            $amount,
            $description,
            $referenceType,
            $referenceId,
            $date,
            $userId
        ) {
            $amount = round((float) $amount, 2);
            $latestEntry = LedgerEntry::where('customer_id', $customer->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $prevBalance = $latestEntry ? (float) $latestEntry->running_balance : (float) $customer->opening_balance;
            $newBalance = round($prevBalance - $amount, 2);

            return LedgerEntry::create([
                'customer_id' => $customer->id,
                'transaction_date' => $date ? date('Y-m-d', strtotime($date)) : now()->toDateString(),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'debit' => 0,
                'credit' => $amount,
                'running_balance' => $newBalance,
                'created_by' => $userId,
            ]);
        });
    }
}
