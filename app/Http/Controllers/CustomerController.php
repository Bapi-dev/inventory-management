<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = Customer::with(['orders', 'payments', 'ledgerEntries']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:distributor,wholesaler,retailer,individual',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'gstin' => 'nullable|string|max:30',
            'credit_limit' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $openingBalance = round((float) ($validated['opening_balance'] ?? 0), 2);

        $customer = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $openingBalance) {
            $customer = Customer::create($validated);

            if ($openingBalance > 0) {
                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'transaction_date' => now()->toDateString(),
                    'reference_type' => 'opening_balance',
                    'reference_id' => null,
                    'description' => 'Opening Balance',
                    'debit' => $openingBalance,
                    'credit' => 0,
                    'running_balance' => $openingBalance,
                    'created_by' => Auth::id(),
                ]);
            }

            return $customer;
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully!');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'orders' => function ($q) {
                $q->latest('order_date')->latest('id');
            },
            'payments' => function ($q) {
                $q->latest('payment_date')->latest('id');
            },
            'ledgerEntries' => function ($q) {
                $q->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');
            }
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:distributor,wholesaler,retailer,individual',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'gstin' => 'nullable|string|max:30',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer profile updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete customer because orders exist for this customer.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully!');
    }
}
