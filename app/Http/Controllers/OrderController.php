<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with('customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->to_date);
        }

        $orders = $query->latest('order_date')->latest('id')->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->with('category')->orderBy('name')->get();
        $selectedCustomerId = $request->customer_id;

        return view('orders.create', compact('customers', 'products', 'selectedCustomerId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:draft,confirmed',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        try {
            $order = $this->orderService->createOrder(
                data: $validated,
                items: $validated['items'],
                userId: Auth::id()
            );

            return redirect()->route('orders.show', $order)
                ->with('success', "Order #{$order->order_number} created successfully!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product.category', 'payments', 'stockMovements']);
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:draft,confirmed,packed,dispatched,delivered,cancelled',
        ]);

        try {
            $newStatus = $request->status;
            $this->orderService->updateStatus($order, $newStatus, Auth::id());

            return back()->with('success', "Order status updated to " . ucfirst($newStatus) . ".");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function print(Order $order)
    {
        $order->load(['customer', 'items.product']);
        $settings = Setting::pluck('value', 'key')->all();

        return view('orders.print', compact('order', 'settings'));
    }
}
