<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected LedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('current_stock', '>', 0)
                      ->whereColumn('current_stock', '<=', 'min_stock_level');
            } elseif ($request->stock_status === 'in_stock') {
                $query->whereColumn('current_stock', '>', 'min_stock_level');
            }
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('inventory.index', compact('products', 'categories', 'customers'));
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'creator']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $movements = $query->latest('date')->latest('id')->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('inventory.movements', compact('movements', 'products'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:stock_in,adjustment,return',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string|max:500',
            'customer_id' => 'nullable|exists:customers,id',
            'credit_amount' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (int) $validated['quantity'];
        $type = $validated['movement_type'];

        // For stock_in and return, quantity should be positive additions
        if ($type === 'stock_in' || $type === 'return') {
            $qty = abs($qty);
        }

        try {
            $refType = match ($type) {
                'stock_in' => 'ManualStockIn',
                'return' => 'CustomerSalesReturn',
                'adjustment' => 'StockAdjustment',
            };

            \Illuminate\Support\Facades\DB::transaction(function () use ($product, $type, $qty, $refType, $validated) {
                $movement = $this->inventoryService->recordMovement(
                    product: $product,
                    movementType: $type,
                    quantity: $qty,
                    referenceType: $refType,
                    referenceId: null,
                    date: now()->toDateTimeString(),
                    notes: $validated['notes'] ?? "Manual {$type}",
                    userId: Auth::id()
                );

                // If it is a customer sales return with a customer and credit amount
                if ($type === 'return' && !empty($validated['customer_id']) && !empty($validated['credit_amount']) && $validated['credit_amount'] > 0) {
                    $customer = Customer::findOrFail($validated['customer_id']);
                    $creditAmt = round((float) $validated['credit_amount'], 2);

                    $this->ledgerService->recordCredit(
                        customer: $customer,
                        amount: $creditAmt,
                        description: "Sales Return: {$qty}x {$product->name}" . ($validated['notes'] ? " ({$validated['notes']})" : ""),
                        referenceType: 'return',
                        referenceId: $movement->id,
                        date: now()->toDateString(),
                        userId: Auth::id()
                    );
                }
            });

            return back()->with('success', 'Inventory updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
