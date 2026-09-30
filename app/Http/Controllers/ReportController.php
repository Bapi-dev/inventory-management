<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        $customerId = $request->customer_id;
        $productId = $request->product_id;

        $query = OrderItem::with(['order.customer', 'product'])
            ->whereHas('order', function ($oq) use ($fromDate, $toDate, $customerId) {
                $oq->whereBetween('order_date', [$fromDate, $toDate])
                   ->whereIn('status', \App\Services\CustomerFinancialService::VALID_SALES_STATUSES);
                if ($customerId) {
                    $oq->where('customer_id', $customerId);
                }
            });

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $items = $query->get();

        $totalQty = $items->sum('quantity');
        $totalSales = $items->sum('subtotal');
        $uniqueOrdersCount = $items->pluck('order_id')->unique()->count();

        // CSV Export
        if ($request->get('export') === 'csv') {
            $response = new StreamedResponse(function () use ($items) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Order Number', 'Date', 'Customer', 'Product', 'Quantity', 'Unit Price', 'Discount', 'Tax', 'Subtotal']);

                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->order->order_number,
                        $item->order->order_date->format('Y-m-d'),
                        $item->order->customer->name,
                        $item->product->name,
                        $item->quantity,
                        $item->unit_price,
                        $item->discount,
                        $item->tax,
                        $item->subtotal,
                    ]);
                }
                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="sales_report_' . date('Ymd_His') . '.csv"');
            return $response;
        }

        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $isPrint = $request->boolean('print');

        return view('reports.sales', compact(
            'items',
            'fromDate',
            'toDate',
            'customerId',
            'productId',
            'totalQty',
            'totalSales',
            'uniqueOrdersCount',
            'customers',
            'products',
            'isPrint'
        ));
    }

    public function payments(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        $customerId = $request->customer_id;
        $method = $request->payment_method;

        $query = Payment::with(['customer', 'order'])
            ->whereBetween('payment_date', [$fromDate, $toDate]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        $payments = $query->latest('payment_date')->get();
        $totalPayments = $payments->sum('amount');

        // CSV Export
        if ($request->get('export') === 'csv') {
            $response = new StreamedResponse(function () use ($payments) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Payment #', 'Date', 'Customer', 'Order #', 'Method', 'Reference #', 'Amount']);

                foreach ($payments as $p) {
                    fputcsv($handle, [
                        $p->payment_number,
                        $p->payment_date->format('Y-m-d'),
                        $p->customer->name,
                        $p->order ? $p->order->order_number : 'N/A',
                        $p->method_label,
                        $p->reference_number ?? '',
                        $p->amount,
                    ]);
                }
                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="payment_report_' . date('Ymd_His') . '.csv"');
            return $response;
        }

        $customers = Customer::orderBy('name')->get();
        $isPrint = $request->boolean('print');

        return view('reports.payments', compact('payments', 'fromDate', 'toDate', 'customerId', 'method', 'totalPayments', 'customers', 'isPrint'));
    }

    public function outstanding(Request $request)
    {
        $customers = Customer::with(['orders', 'payments', 'ledgerEntries'])->get();

        $rows = [];
        foreach ($customers as $cust) {
            $bal = $cust->outstanding_balance;
            if ($bal > 0) {
                $rows[] = [
                    'customer' => $cust,
                    'total_sales' => $cust->total_sales,
                    'total_paid' => $cust->total_paid,
                    'outstanding' => $bal,
                ];
            }
        }

        usort($rows, fn($a, $b) => $b['outstanding'] <=> $a['outstanding']);
        $totalOutstanding = array_sum(array_column($rows, 'outstanding'));

        if ($request->get('export') === 'csv') {
            $response = new StreamedResponse(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Customer Name', 'Type', 'Mobile', 'Total Sales', 'Total Paid', 'Outstanding Balance']);

                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row['customer']->name,
                        ucfirst($row['customer']->customer_type),
                        $row['customer']->mobile,
                        $row['total_sales'],
                        $row['total_paid'],
                        $row['outstanding'],
                    ]);
                }
                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="outstanding_report_' . date('Ymd_His') . '.csv"');
            return $response;
        }

        $isPrint = $request->boolean('print');
        return view('reports.outstanding', compact('rows', 'totalOutstanding', 'isPrint'));
    }

    public function stock(Request $request)
    {
        $products = Product::with(['category', 'stockMovements'])->orderBy('name')->get();

        $rows = [];
        foreach ($products as $p) {
            $openingStock = abs($p->stockMovements->where('movement_type', 'opening_stock')->sum('quantity'));
            $stockIn = abs($p->stockMovements->where('movement_type', 'stock_in')->sum('quantity'));
            $sold = abs($p->stockMovements->where('movement_type', 'sale')->sum('quantity'));
            $returns = abs($p->stockMovements->where('movement_type', 'return')->sum('quantity'));
            $adjustments = $p->stockMovements->where('movement_type', 'adjustment')->sum('quantity');

            $rows[] = [
                'product' => $p,
                'opening_stock' => $openingStock,
                'stock_in' => $stockIn,
                'sold' => $sold,
                'returns' => $returns,
                'adjustments' => $adjustments,
                'current_stock' => $p->current_stock,
                'status' => $p->stock_status,
            ];
        }

        if ($request->get('export') === 'csv') {
            $response = new StreamedResponse(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Product Name', 'SKU', 'Category', 'Opening Stock', 'Stock In', 'Sold', 'Returns', 'Adjustments', 'Current Stock', 'Status']);

                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row['product']->name,
                        $row['product']->sku,
                        $row['product']->category->name,
                        $row['opening_stock'],
                        $row['stock_in'],
                        $row['sold'],
                        $row['returns'],
                        $row['adjustments'],
                        $row['current_stock'],
                        $row['status'],
                    ]);
                }
                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="stock_report_' . date('Ymd_His') . '.csv"');
            return $response;
        }

        $isPrint = $request->boolean('print');
        return view('reports.stock', compact('rows', 'isPrint'));
    }
}
