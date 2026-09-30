<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'order', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->withQueryString();
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('payments.index', compact('payments', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $selectedCustomerId = $request->customer_id;
        $selectedOrderId = $request->order_id;
        $order = null;

        if ($selectedOrderId) {
            $order = Order::with('customer')->find($selectedOrderId);
            if ($order && !$selectedCustomerId) {
                $selectedCustomerId = $order->customer_id;
            }
        }

        return view('payments.create', compact('customers', 'selectedCustomerId', 'selectedOrderId', 'order'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'nullable|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,upi,cheque,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->paymentService->recordPayment($validated, Auth::id());

            if ($request->has('redirect_to_order') && $payment->order_id) {
                return redirect()->route('orders.show', $payment->order_id)
                    ->with('success', "Payment #{$payment->payment_number} recorded successfully!");
            }

            return redirect()->route('payments.receipt', $payment)
                ->with('success', "Payment #{$payment->payment_number} recorded successfully!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['customer', 'order', 'creator']);
        $settings = Setting::pluck('value', 'key')->all();

        // Calculate customer's outstanding balance at or around this payment
        $currentOutstanding = $payment->customer->outstanding_balance;
        $previousOutstanding = $currentOutstanding + (float) $payment->amount;

        return view('payments.receipt', compact('payment', 'settings', 'previousOutstanding', 'currentOutstanding'));
    }
}
