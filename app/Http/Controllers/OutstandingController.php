<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OutstandingController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['orders', 'payments', 'ledgerEntries']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $allCustomers = $query->get();

        $rows = [];
        foreach ($allCustomers as $customer) {
            $outstanding = $customer->outstanding_balance;
            $totalSales = $customer->total_sales;
            $totalPaid = $customer->total_paid;

            $lastPayment = $customer->payments->sortByDesc('payment_date')->first();

            // Calculate days outstanding based on earliest unpaid order or latest transaction
            $earliestUnpaidOrder = $customer->orders
                ->whereIn('payment_status', ['unpaid', 'partially_paid'])
                ->whereIn('status', \App\Services\CustomerFinancialService::VALID_SALES_STATUSES)
                ->sortBy('order_date')
                ->first();

            $daysOutstanding = 0;
            if ($outstanding > 0) {
                if ($earliestUnpaidOrder) {
                    $daysOutstanding = Carbon::parse($earliestUnpaidOrder->order_date)->diffInDays(now());
                } else {
                    $latestLedger = $customer->ledgerEntries->last();
                    $daysOutstanding = $latestLedger ? Carbon::parse($latestLedger->transaction_date)->diffInDays(now()) : 0;
                }
            }

            $rows[] = [
                'customer' => $customer,
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
                'outstanding' => $outstanding,
                'last_payment_date' => $lastPayment ? $lastPayment->payment_date->format('d M Y') : 'Never',
                'last_payment_amount' => $lastPayment ? $lastPayment->amount : 0,
                'days_outstanding' => $daysOutstanding,
                'has_partially_paid' => $customer->orders->contains('payment_status', 'partially_paid'),
            ];
        }

        // Filter
        $filter = $request->get('filter', 'due');
        if ($filter === 'due') {
            $rows = array_filter($rows, fn($r) => $r['outstanding'] > 0);
        } elseif ($filter === 'partially_paid') {
            $rows = array_filter($rows, fn($r) => $r['has_partially_paid']);
        } elseif ($filter === 'overdue') {
            $rows = array_filter($rows, fn($r) => $r['outstanding'] > 0 && $r['days_outstanding'] >= 30);
        }

        // Sort
        $sort = $request->get('sort', 'highest_outstanding');
        if ($sort === 'highest_outstanding') {
            usort($rows, fn($a, $b) => $b['outstanding'] <=> $a['outstanding']);
        } elseif ($sort === 'oldest_due') {
            usort($rows, fn($a, $b) => $b['days_outstanding'] <=> $a['days_outstanding']);
        } elseif ($sort === 'customer_name') {
            usort($rows, fn($a, $b) => strcasecmp($a['customer']->name, $b['customer']->name));
        }

        $totalOutstandingSum = array_sum(array_column($rows, 'outstanding'));

        return view('outstanding.index', compact('rows', 'filter', 'sort', 'totalOutstandingSum'));
    }
}
