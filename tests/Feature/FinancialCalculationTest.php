<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerFinancialService;
use App\Services\InventoryService;
use App\Services\LedgerService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FinancialCalculationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Category $category;
    protected Product $product;
    protected OrderService $orderService;
    protected PaymentService $paymentService;
    protected LedgerService $ledgerService;
    protected CustomerFinancialService $financialService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $this->actingAs($this->user);

        $this->orderService = app(OrderService::class);
        $this->paymentService = app(PaymentService::class);
        $this->ledgerService = app(LedgerService::class);
        $this->financialService = app(CustomerFinancialService::class);

        $this->category = Category::firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category', 'description' => 'For testing']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-BAG-001'],
            [
                'name' => 'Test Premium Backpack',
                'category_id' => $this->category->id,
                'selling_price' => 1500.00,
                'wholesale_price' => 1000.00,
                'current_stock' => 1000,
                'min_stock_level' => 10,
                'status' => 'active',
            ]
        );
    }

    protected function createTestCustomer(string $name, float $openingBalance = 0): Customer
    {
        $customer = Customer::create([
            'name' => $name,
            'customer_type' => 'wholesaler',
            'mobile' => '+91 99999 ' . rand(10000, 99999),
            'email' => strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@test.com',
            'credit_limit' => 50000.00,
            'opening_balance' => $openingBalance,
            'status' => 'active',
        ]);

        if ($openingBalance > 0) {
            $this->ledgerService->recordDebit(
                customer: $customer,
                amount: $openingBalance,
                description: 'Opening Balance',
                referenceType: 'opening_balance',
                referenceId: null,
                date: now()->toDateString(),
                userId: $this->user->id
            );
        }

        return $customer;
    }

    /**
     * Test 1 — No Payment
     * Sales = ₹14,250, Paid = ₹0 -> Outstanding = ₹14,250
     */
    public function test_1_no_payment_outstanding_equals_total_sales(): void
    {
        $customer = $this->createTestCustomer('Test Customer 1');

        $order = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
                'discount' => 0,
                'tax' => 0,
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->assertEquals(14250.00, $order->grand_total);
        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(0.00, $customer->total_paid);
        $this->assertEquals(14250.00, $customer->outstanding_balance);
        $this->assertEquals(14250.00, $this->financialService->getClosingLedgerBalance($customer));
    }

    /**
     * Test 2 — Partial Payment
     * Sales = ₹14,250, Paid = ₹10,000 -> Outstanding = ₹4,250
     */
    public function test_2_partial_payment_reduces_outstanding(): void
    {
        $customer = $this->createTestCustomer('Test Customer 2');

        $order = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(10000.00, $customer->total_paid);
        $this->assertEquals(4250.00, $customer->outstanding_balance);
        $this->assertEquals(4250.00, $this->financialService->getClosingLedgerBalance($customer));
        $this->assertEquals(4250.00, $order->fresh()->outstanding_amount);
        $this->assertEquals(Order::PAYMENT_PARTIALLY_PAID, $order->fresh()->payment_status);
    }

    /**
     * Test 3 — Full Payment
     * Sales = ₹14,250, Paid = ₹14,250 -> Outstanding = ₹0
     */
    public function test_3_full_payment_settles_account(): void
    {
        $customer = $this->createTestCustomer('Test Customer 3');

        $order = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 14250.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'upi',
            'reference_number' => 'UPI-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(14250.00, $customer->total_paid);
        $this->assertEquals(0.00, $customer->outstanding_balance);
        $this->assertEquals(0.00, $this->financialService->getClosingLedgerBalance($customer));
        $this->assertEquals(0.00, $order->fresh()->outstanding_amount);
        $this->assertEquals(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }

    /**
     * Test 4 — Multiple Payments
     * Sales = ₹14,250, Payments: 5k, 3k, 6.25k -> Total Paid = ₹14,250, Outstanding = ₹0
     */
    public function test_4_multiple_payments_settle_order_and_customer(): void
    {
        $customer = $this->createTestCustomer('Test Customer 4');

        $order = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 5000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], $this->user->id);

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 3000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 6250.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'upi',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(14250.00, $customer->total_paid);
        $this->assertEquals(0.00, $customer->outstanding_balance);
        $this->assertEquals(0.00, $this->financialService->getClosingLedgerBalance($customer));
        $this->assertEquals(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }

    /**
     * Test 5 — Opening Balance
     * Opening Balance = ₹5,000, Sales = ₹14,250, Paid = ₹10,000 -> Outstanding = ₹9,250
     */
    public function test_5_opening_balance_included_correctly(): void
    {
        $customer = $this->createTestCustomer('Test Customer 5', 5000.00);

        $order = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cheque',
            'reference_number' => 'CHQ-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        $this->assertEquals(5000.00, $customer->opening_balance);
        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(10000.00, $customer->total_paid);
        $this->assertEquals(9250.00, $customer->outstanding_balance);
        $this->assertEquals(9250.00, $this->financialService->getClosingLedgerBalance($customer));
    }

    /**
     * Test 6 — Sales Return
     * Sales = ₹14,250, Return = ₹2,000, Paid = ₹10,000 -> Outstanding = ₹2,250
     */
    public function test_6_sales_return_reduces_outstanding(): void
    {
        $customer = $this->createTestCustomer('Test Customer 6');

        $order = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        // Record a sales return credit in ledger
        $this->ledgerService->recordCredit(
            customer: $customer,
            amount: 2000.00,
            description: 'Sales Return: 2x Defective Bags',
            referenceType: 'return',
            referenceId: null,
            date: now()->toDateString(),
            userId: $this->user->id
        );

        $customer->refresh();

        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(10000.00, $customer->total_paid);
        $this->assertEquals(2000.00, $this->financialService->getTotalReturns($customer));
        $this->assertEquals(2250.00, $customer->outstanding_balance);
        $this->assertEquals(2250.00, $this->financialService->getClosingLedgerBalance($customer));
    }

    /**
     * Test 7 — Cancelled Order
     * Sales = ₹14,250, Cancelled Order = ₹5,000, Paid = ₹10,000 -> Cancelled order is excluded
     */
    public function test_7_cancelled_order_excluded_from_sales_and_outstanding(): void
    {
        $customer = $this->createTestCustomer('Test Customer 7');

        // Order 1: ₹14,250 (Active)
        $order1 = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 14, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        // Order 2: ₹5,000 (To be cancelled)
        $order2 = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        // Cancel order 2
        $this->orderService->updateStatus($order2, 'cancelled', $this->user->id);

        // Payment: ₹10,000 against Order 1
        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order1->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        // Total sales must only be Order 1 (14,250), Order 2 (5,000) must be excluded!
        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(10000.00, $customer->total_paid);
        $this->assertEquals(4250.00, $customer->outstanding_balance);
        $this->assertEquals(4250.00, $this->financialService->getClosingLedgerBalance($customer));
    }

    /**
     * Test 8 — Multiple Customers
     * Verify that transactions belonging to one customer never affect another customer's balance.
     */
    public function test_8_multiple_customers_isolation(): void
    {
        $customerA = $this->createTestCustomer('Customer A');
        $customerB = $this->createTestCustomer('Customer B');

        // Order for Customer A: ₹10,000
        $this->orderService->createOrder(
            data: [
                'customer_id' => $customerA->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        // Order for Customer B: ₹5,000
        $this->orderService->createOrder(
            data: [
                'customer_id' => $customerB->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        // Payment for Customer A: ₹10,000
        $this->paymentService->recordPayment([
            'customer_id' => $customerA->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], $this->user->id);

        $customerA->refresh();
        $customerB->refresh();

        $this->assertEquals(10000.00, $customerA->total_sales);
        $this->assertEquals(10000.00, $customerA->total_paid);
        $this->assertEquals(0.00, $customerA->outstanding_balance);

        $this->assertEquals(5000.00, $customerB->total_sales);
        $this->assertEquals(0.00, $customerB->total_paid);
        $this->assertEquals(5000.00, $customerB->outstanding_balance);
    }

    /**
     * Test 9 — Multiple Orders
     * Order 1 = ₹10,000, Order 2 = ₹4,250 -> Total Sales = ₹14,250
     */
    public function test_9_multiple_orders_aggregate_correctly(): void
    {
        $customer = $this->createTestCustomer('Test Customer 9');

        $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'packed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 4, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $customer->refresh();

        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(0.00, $customer->total_paid);
        $this->assertEquals(14250.00, $customer->outstanding_balance);
        $this->assertEquals(14250.00, $this->financialService->getClosingLedgerBalance($customer));
    }

    /**
     * Test 10 — Multiple Payments Across Orders
     * Verify that customer-level payment totals and order-level payment totals remain logically consistent.
     */
    public function test_10_multiple_payments_across_orders(): void
    {
        $customer = $this->createTestCustomer('Test Customer 10');

        $order1 = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $order2 = $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 4, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 250.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        // Pay Order 1 in full (10,000)
        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order1->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        // Pay Order 2 partially (2,000)
        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'order_id' => $order2->id,
            'amount' => 2000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'upi',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        $this->assertEquals(14250.00, $customer->total_sales);
        $this->assertEquals(12000.00, $customer->total_paid);
        $this->assertEquals(2250.00, $customer->outstanding_balance);
        $this->assertEquals(2250.00, $this->financialService->getClosingLedgerBalance($customer));

        $this->assertEquals(0.00, $order1->fresh()->outstanding_amount);
        $this->assertEquals(Order::PAYMENT_PAID, $order1->fresh()->payment_status);

        $this->assertEquals(2250.00, $order2->fresh()->outstanding_amount);
        $this->assertEquals(Order::PAYMENT_PARTIALLY_PAID, $order2->fresh()->payment_status);
    }

    /**
     * Test 11 — Overpayment / Advance Credit Handling (Section 26)
     * Sales = ₹10,000, Paid = ₹12,000 -> Outstanding = ₹0, Advance Credit = ₹2,000
     */
    public function test_11_overpayment_displays_zero_outstanding_and_advance_credit(): void
    {
        $customer = $this->createTestCustomer('Test Customer 11');

        $this->orderService->createOrder(
            data: [
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => 'confirmed',
            ],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 1000.00, 'discount' => 0, 'tax' => 0],
            ],
            userId: $this->user->id
        );

        $this->paymentService->recordPayment([
            'customer_id' => $customer->id,
            'amount' => 12000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-' . uniqid(),
        ], $this->user->id);

        $customer->refresh();

        $this->assertEquals(10000.00, $customer->total_sales);
        $this->assertEquals(12000.00, $customer->total_paid);
        // Outstanding must be 0, not -2000!
        $this->assertEquals(0.00, $customer->outstanding_balance);
        $this->assertEquals(2000.00, $customer->credit_balance);
        $this->assertEquals(-2000.00, $customer->net_balance);
    }

    /**
     * Test 12 — Seeded Customer 'Classic Wholesale Traders' Reconciliation (Section 29 & 31)
     * Verifies that the live seeded customer reconciles across Customer List, Details, Ledger, Outstanding Report, and Dashboard.
     */
    public function test_12_classic_wholesale_traders_cross_page_consistency(): void
    {
        $classic = Customer::where('name', 'Classic Wholesale Traders')->first();
        $this->assertNotNull($classic, "Classic Wholesale Traders must exist in database");

        // Mathematical verification
        $this->assertEquals(14250.00, $classic->total_sales);
        $this->assertEquals(10000.00, $classic->total_paid);
        $this->assertEquals(4250.00, $classic->outstanding_balance);
        $this->assertEquals(4250.00, $this->financialService->getClosingLedgerBalance($classic));

        // Customer List Page Test
        $listResponse = $this->get(route('customers.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Classic Wholesale Traders');
        $listResponse->assertSee('₹14,250.00');
        $listResponse->assertSee('₹10,000.00');
        $listResponse->assertSee('₹4,250.00');

        // Customer Details Page Test
        $showResponse = $this->get(route('customers.show', $classic));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('₹14,250.00');
        $showResponse->assertSee('₹10,000.00');
        $showResponse->assertSee('₹4,250.00');

        // Outstanding Receivables Page Test
        $outstandingResponse = $this->get(route('outstanding.index'));
        $outstandingResponse->assertStatus(200);
        $outstandingResponse->assertSee('Classic Wholesale Traders');
        $outstandingResponse->assertSee('₹4,250.00');

        // Outstanding Report Page Test
        $reportResponse = $this->get(route('reports.outstanding'));
        $reportResponse->assertStatus(200);
        $reportResponse->assertSee('Classic Wholesale Traders');
        $reportResponse->assertSee('₹4,250.00');

        // Dashboard Page Test: Checks Recent Orders and aggregate Total Outstanding
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Classic Wholesale Traders');
        $dashboardResponse->assertSee('ORD-2026-0012');
        $totalExpectedOutstanding = Customer::all()->sum(fn($c) => $c->outstanding_balance);
        $dashboardResponse->assertSee('₹' . number_format($totalExpectedOutstanding, 2));
    }
}
