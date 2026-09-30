<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Metric Cards
        $totalProducts = Product::count();
        $currentStockQty = Product::sum('current_stock');

        $validSales = \App\Services\CustomerFinancialService::VALID_SALES_STATUSES;

        $todaysOrdersCount = Order::whereDate('order_date', $today)->count();
        $pendingOrdersCount = Order::whereIn('status', ['draft', 'confirmed', 'packed'])->count();

        $todaysSales = (float) Order::whereDate('order_date', $today)
            ->whereIn('status', $validSales)
            ->sum('grand_total');

        $paymentsReceivedToday = (float) Payment::whereDate('payment_date', $today)->sum('amount');

        $lowStockProductsCount = Product::whereColumn('current_stock', '<=', 'min_stock_level')->count();

        // Total Outstanding calculated across all customers using single financial service
        $customers = Customer::with(['orders', 'payments', 'ledgerEntries'])->get();

        $totalOutstanding = 0;
        $customersWithBalances = [];

        foreach ($customers as $cust) {
            $bal = $cust->outstanding_balance;
            if ($bal > 0) {
                $totalOutstanding += $bal;
                $customersWithBalances[] = [
                    'customer' => $cust,
                    'balance' => $bal,
                ];
            }
        }

        // Top 5 Outstanding Customers
        usort($customersWithBalances, fn($a, $b) => $b['balance'] <=> $a['balance']);
        $topOutstandingCustomers = array_slice($customersWithBalances, 0, 5);

        // 2. Recent Orders (6)
        $recentOrders = Order::with('customer')
            ->latest('order_date')
            ->latest('id')
            ->take(6)
            ->get();

        // 3. Low Stock Products (up to 6)
        $lowStockProducts = Product::with('category')
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->orderBy('current_stock', 'asc')
            ->take(6)
            ->get();

        // 4. Sales Summary
        $salesToday = $todaysSales;
        $salesThisWeek = (float) Order::where('order_date', '>=', $startOfWeek)
            ->whereIn('status', $validSales)
            ->sum('grand_total');
        $salesThisMonth = (float) Order::where('order_date', '>=', $startOfMonth)
            ->whereIn('status', $validSales)
            ->sum('grand_total');

        // 5. Chart Data: Last 7 Days Sales Trend
        $chartLabels = [];
        $chartSales = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D (d M)');
            $dailySum = (float) Order::whereDate('order_date', $date)
                ->whereIn('status', $validSales)
                ->sum('grand_total');
            $chartSales[] = $dailySum;
        }

        return view('dashboard.index', compact(
            'totalProducts',
            'currentStockQty',
            'todaysOrdersCount',
            'pendingOrdersCount',
            'todaysSales',
            'totalOutstanding',
            'paymentsReceivedToday',
            'lowStockProductsCount',
            'recentOrders',
            'topOutstandingCustomers',
            'lowStockProducts',
            'salesToday',
            'salesThisWeek',
            'salesThisMonth',
            'chartLabels',
            'chartSales'
        ));
    }
}
