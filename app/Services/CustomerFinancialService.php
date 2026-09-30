<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CustomerFinancialService
{
    /**
     * Order statuses that represent legally binding sales debt.
     */
    public const VALID_SALES_STATUSES = ['confirmed', 'packed', 'dispatched', 'delivered'];

    /**
     * Order statuses excluded from sales and receivables.
     */
    public const EXCLUDED_SALES_STATUSES = ['draft', 'cancelled'];

    /**
     * Get opening balance for customer.
     */
    public function getOpeningBalance(Customer $customer): float
    {
        return round((float) ($customer->opening_balance ?? 0), 2);
    }

    /**
     * Get total confirmed/delivered sales amount.
     * Uses in-memory collection when eager loaded, otherwise executes database query.
     */
    public function getTotalSales(Customer $customer): float
    {
        if ($customer->relationLoaded('orders')) {
            $sum = $customer->orders
                ->whereIn('status', self::VALID_SALES_STATUSES)
                ->sum('grand_total');
        } else {
            $sum = $customer->orders()
                ->whereIn('status', self::VALID_SALES_STATUSES)
                ->sum('grand_total');
        }

        return round((float) $sum, 2);
    }

    /**
     * Get total valid payments received from customer.
     */
    public function getTotalPaid(Customer $customer): float
    {
        if ($customer->relationLoaded('payments')) {
            $sum = $customer->payments->sum('amount');
        } else {
            $sum = $customer->payments()->sum('amount');
        }

        return round((float) $sum, 2);
    }

    /**
     * Get total sales return credit recorded for customer.
     */
    public function getTotalReturns(Customer $customer): float
    {
        if ($customer->relationLoaded('ledgerEntries')) {
            $sum = $customer->ledgerEntries
                ->where('reference_type', 'return')
                ->sum('credit');
        } else {
            $sum = $customer->ledgerEntries()
                ->where('reference_type', 'return')
                ->sum('credit');
        }

        return round((float) $sum, 2);
    }

    /**
     * Get net adjustment amount (Debit adjustments minus Credit adjustments).
     */
    public function getNetAdjustments(Customer $customer): float
    {
        if ($customer->relationLoaded('ledgerEntries')) {
            $debits = $customer->ledgerEntries
                ->where('reference_type', 'adjustment')
                ->sum('debit');
            $credits = $customer->ledgerEntries
                ->where('reference_type', 'adjustment')
                ->sum('credit');
        } else {
            $debits = $customer->ledgerEntries()
                ->where('reference_type', 'adjustment')
                ->sum('debit');
            $credits = $customer->ledgerEntries()
                ->where('reference_type', 'adjustment')
                ->sum('credit');
        }

        return round((float) ($debits - $credits), 2);
    }

    /**
     * Calculate true mathematical net balance:
     * Opening Balance + Valid Sales - Total Paid - Sales Returns + Net Adjustments.
     */
    public function getNetBalance(Customer $customer): float
    {
        $opening = $this->getOpeningBalance($customer);
        $sales = $this->getTotalSales($customer);
        $paid = $this->getTotalPaid($customer);
        $returns = $this->getTotalReturns($customer);
        $adjustments = $this->getNetAdjustments($customer);

        return round($opening + $sales - $paid - $returns + $adjustments, 2);
    }

    /**
     * Customer Outstanding Receivables (amount customer owes us).
     * If customer has overpaid or has advance credit, outstanding is ₹0.00.
     */
    public function getOutstanding(Customer $customer): float
    {
        $net = $this->getNetBalance($customer);
        return max(0.00, $net);
    }

    /**
     * Customer Advance / Credit Balance (when total paid/returns exceed billed sales).
     */
    public function getCreditBalance(Customer $customer): float
    {
        $net = $this->getNetBalance($customer);
        return $net < 0 ? round(abs($net), 2) : 0.00;
    }

    /**
     * Calculate available credit under assigned credit limit.
     */
    public function getAvailableCredit(Customer $customer): float
    {
        $creditLimit = (float) ($customer->credit_limit ?? 0);
        if ($creditLimit <= 0) {
            return 0.00;
        }

        $outstanding = $this->getOutstanding($customer);
        $available = $creditLimit - $outstanding;

        return max(0.00, round($available, 2));
    }

    /**
     * Get the latest running balance directly from the double-sided ledger.
     */
    public function getClosingLedgerBalance(Customer $customer): float
    {
        $latest = LedgerEntry::where('customer_id', $customer->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            return round((float) $latest->running_balance, 2);
        }

        return $this->getOpeningBalance($customer);
    }

    /**
     * Get a complete financial health summary for a customer.
     */
    public function getSummary(Customer $customer): array
    {
        $opening = $this->getOpeningBalance($customer);
        $sales = $this->getTotalSales($customer);
        $paid = $this->getTotalPaid($customer);
        $returns = $this->getTotalReturns($customer);
        $adjustments = $this->getNetAdjustments($customer);
        $netBalance = round($opening + $sales - $paid - $returns + $adjustments, 2);
        $outstanding = max(0.00, $netBalance);
        $creditBalance = $netBalance < 0 ? round(abs($netBalance), 2) : 0.00;
        $availableCredit = $this->getAvailableCredit($customer);
        $closingLedger = $this->getClosingLedgerBalance($customer);

        return [
            'opening_balance' => $opening,
            'total_sales' => $sales,
            'total_paid' => $paid,
            'total_returns' => $returns,
            'net_adjustments' => $adjustments,
            'net_balance' => $netBalance,
            'outstanding' => $outstanding,
            'credit_balance' => $creditBalance,
            'available_credit' => $availableCredit,
            'closing_ledger_balance' => $closingLedger,
            'is_in_sync' => abs($netBalance - $closingLedger) < 0.01,
        ];
    }

    /**
     * Reconcile and recalculate running balance across all ledger entries for a customer.
     */
    public function reconcileCustomerLedger(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $entries = LedgerEntry::where('customer_id', $customer->id)
                ->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $running = 0.00;
            $hasOpeningEntry = false;

            foreach ($entries as $entry) {
                if ($entry->reference_type === 'opening_balance') {
                    $hasOpeningEntry = true;
                    $running = (float) $entry->debit;
                } else {
                    if (!$hasOpeningEntry && $customer->opening_balance > 0) {
                        $running = (float) $customer->opening_balance;
                        $hasOpeningEntry = true;
                    }
                    $running += ((float) $entry->debit - (float) $entry->credit);
                }

                $entry->running_balance = round($running, 2);
                $entry->save();
            }
        });
    }
}
