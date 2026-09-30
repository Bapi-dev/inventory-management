<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@bagswholesale.com'],
            [
                'name' => 'Admin Manager',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Business Settings
        $defaultSettings = [
            'business_name' => 'BagSmart Wholesale & Manufacturing Ltd.',
            'business_address' => 'Plot #42, Industrial Area, Sector 7, Andheri East, Mumbai, Maharashtra 400093',
            'business_mobile' => '+91 98200 12345',
            'business_email' => 'sales@bagsmart.com',
            'business_gstin' => '27ABCDE1234F1Z5',
            'currency_symbol' => '₹',
            'invoice_terms' => '1. Goods once sold will not be returned unless manufacturing defect is notified within 7 days. 2. 18% annual interest charged on overdue balances exceeding 30 days. 3. Subject to Mumbai Jurisdiction.',
        ];

        foreach ($defaultSettings as $k => $v) {
            Setting::set($k, $v);
        }

        // 3. Categories (8 categories)
        $categoriesData = [
            ['name' => 'Backpack', 'description' => 'Everyday commuter and college backpacks'],
            ['name' => 'Rucksack', 'description' => 'Heavy duty trekking, hiking, and travel rucksacks'],
            ['name' => 'Tote Bag', 'description' => 'Canvas, cotton, and eco-friendly shopping totes'],
            ['name' => 'School Bag', 'description' => 'Ergonomic, lightweight student backpacks'],
            ['name' => 'Laptop Bag', 'description' => 'Executive briefcases and padded laptop sleeves'],
            ['name' => 'Travel Bag', 'description' => 'Duffel bags, weekender bags, and gym holdalls'],
            ['name' => 'Suitcase', 'description' => 'Hard-shell polycarbonate and soft trolley luggage'],
            ['name' => 'Other Bags', 'description' => 'Sling bags, waist pouches, and accessories'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::firstOrCreate(
                ['name' => $cat['name']],
                ['slug' => Str::slug($cat['name']), 'description' => $cat['description']]
            );
        }

        // 4. Products (18 realistic products)
        $productsData = [
            [
                'name' => 'Classic Backpack',
                'sku' => 'BP-001',
                'category' => 'Backpack',
                'description' => 'Durable water-resistant 25L polyester backpack with dual bottle pockets.',
                'size' => '25 Litres',
                'color' => 'Navy Blue',
                'unit' => 'Pcs',
                'wholesale_price' => 650.00,
                'selling_price' => 1100.00,
                'min_stock_level' => 30,
                'initial_stock' => 250,
            ],
            [
                'name' => 'Pro Laptop Backpack',
                'sku' => 'BP-002',
                'category' => 'Backpack',
                'description' => 'Padded 15.6" laptop compartment with USB charging port and anti-theft zipper.',
                'size' => '30 Litres',
                'color' => 'Carbon Black',
                'unit' => 'Pcs',
                'wholesale_price' => 950.00,
                'selling_price' => 1650.00,
                'min_stock_level' => 25,
                'initial_stock' => 180,
            ],
            [
                'name' => 'Waterproof Trekking Backpack',
                'sku' => 'BP-003',
                'category' => 'Backpack',
                'description' => 'All-weather mountain trekking pack with rain cover.',
                'size' => '35 Litres',
                'color' => 'Forest Green',
                'unit' => 'Pcs',
                'wholesale_price' => 1200.00,
                'selling_price' => 1999.00,
                'min_stock_level' => 20,
                'initial_stock' => 12, // Low stock
            ],
            [
                'name' => '55L Explorer Rucksack',
                'sku' => 'RK-001',
                'category' => 'Rucksack',
                'description' => 'Heavy-duty nylon rucksack with internal metal frame and ergonomic hip belt.',
                'size' => '55 Litres',
                'color' => 'Camouflage Green',
                'unit' => 'Pcs',
                'wholesale_price' => 1450.00,
                'selling_price' => 2499.00,
                'min_stock_level' => 20,
                'initial_stock' => 140,
            ],
            [
                'name' => '70L Expedition Rucksack',
                'sku' => 'RK-002',
                'category' => 'Rucksack',
                'description' => 'High-altitude expedition bag with sleeping bag compartment.',
                'size' => '70 Litres',
                'color' => 'Crimson Red',
                'unit' => 'Pcs',
                'wholesale_price' => 1850.00,
                'selling_price' => 2999.00,
                'min_stock_level' => 15,
                'initial_stock' => 75,
            ],
            [
                'name' => 'Ultralight Trail Rucksack',
                'sku' => 'RK-003',
                'category' => 'Rucksack',
                'description' => 'Ripstop ultralight hiking pack for weekend campers.',
                'size' => '45 Litres',
                'color' => 'Electric Blue',
                'unit' => 'Pcs',
                'wholesale_price' => 1100.00,
                'selling_price' => 1850.00,
                'min_stock_level' => 15,
                'initial_stock' => 8, // Low stock
            ],
            [
                'name' => 'Eco Canvas Tote Bag',
                'sku' => 'TB-001',
                'category' => 'Tote Bag',
                'description' => '100% natural organic cotton canvas tote bag with reinforced stitching.',
                'size' => '16 x 14 Inch',
                'color' => 'Natural Beige',
                'unit' => 'Pcs',
                'wholesale_price' => 180.00,
                'selling_price' => 399.00,
                'min_stock_level' => 50,
                'initial_stock' => 500,
            ],
            [
                'name' => 'Premium Leatherette Tote',
                'sku' => 'TB-002',
                'category' => 'Tote Bag',
                'description' => 'Sophisticated vegan leather handbag tote for shopping and office.',
                'size' => 'Large',
                'color' => 'Tan Brown',
                'unit' => 'Pcs',
                'wholesale_price' => 450.00,
                'selling_price' => 899.00,
                'min_stock_level' => 30,
                'initial_stock' => 220,
            ],
            [
                'name' => 'Organic Cotton Shopper Tote',
                'sku' => 'TB-003',
                'category' => 'Tote Bag',
                'description' => 'Screen-printed floral eco tote bag.',
                'size' => 'Medium',
                'color' => 'Ivory White',
                'unit' => 'Pcs',
                'wholesale_price' => 150.00,
                'selling_price' => 299.00,
                'min_stock_level' => 40,
                'initial_stock' => 0, // Out of stock
            ],
            [
                'name' => 'Junior Ergonomic School Bag',
                'sku' => 'SB-001',
                'category' => 'School Bag',
                'description' => 'Orthopedic back support with reflective safety strips for school kids.',
                'size' => 'Small (1-5th)',
                'color' => 'Red / Yellow',
                'unit' => 'Pcs',
                'wholesale_price' => 520.00,
                'selling_price' => 950.00,
                'min_stock_level' => 50,
                'initial_stock' => 350,
            ],
            [
                'name' => 'High School Heavy Duty Bag',
                'sku' => 'SB-002',
                'category' => 'School Bag',
                'description' => 'Triple compartment book bag with lunchbox pouch.',
                'size' => 'Medium (6-10th)',
                'color' => 'Teal / Grey',
                'unit' => 'Pcs',
                'wholesale_price' => 680.00,
                'selling_price' => 1200.00,
                'min_stock_level' => 40,
                'initial_stock' => 280,
            ],
            [
                'name' => 'Slim Executive Laptop Bag',
                'sku' => 'LP-001',
                'category' => 'Laptop Bag',
                'description' => 'Waterproof ballistic nylon briefcase with detachable shoulder strap.',
                'size' => '14 Inch',
                'color' => 'Charcoal Grey',
                'unit' => 'Pcs',
                'wholesale_price' => 850.00,
                'selling_price' => 1499.00,
                'min_stock_level' => 25,
                'initial_stock' => 190,
            ],
            [
                'name' => 'Multi-compartment Office Bag',
                'sku' => 'LP-002',
                'category' => 'Laptop Bag',
                'description' => 'Expandable files and documents laptop messenger bag.',
                'size' => '15.6 Inch',
                'color' => 'Midnight Black',
                'unit' => 'Pcs',
                'wholesale_price' => 1050.00,
                'selling_price' => 1799.00,
                'min_stock_level' => 25,
                'initial_stock' => 160,
            ],
            [
                'name' => '45L Duffel Travel Bag',
                'sku' => 'TR-001',
                'category' => 'Travel Bag',
                'description' => 'Gym and weekend travel duffel with isolated shoe compartment.',
                'size' => '45 Litres',
                'color' => 'Grey / Orange',
                'unit' => 'Pcs',
                'wholesale_price' => 750.00,
                'selling_price' => 1350.00,
                'min_stock_level' => 30,
                'initial_stock' => 210,
            ],
            [
                'name' => 'Weekender Travel Bag',
                'sku' => 'TR-002',
                'category' => 'Travel Bag',
                'description' => 'Heavy canvas & brass fitting classic travel carry-on bag.',
                'size' => '40 Litres',
                'color' => 'Army Green',
                'unit' => 'Pcs',
                'wholesale_price' => 620.00,
                'selling_price' => 1150.00,
                'min_stock_level' => 20,
                'initial_stock' => 130,
            ],
            [
                'name' => 'Hard Shell Cabin Suitcase',
                'sku' => 'SU-001',
                'category' => 'Suitcase',
                'description' => '20-inch unbreakable polycarbonate suitcase with 360-degree silent spinner wheels.',
                'size' => '20 Inch Cabin',
                'color' => 'Metallic Silver',
                'unit' => 'Pcs',
                'wholesale_price' => 2200.00,
                'selling_price' => 3800.00,
                'min_stock_level' => 15,
                'initial_stock' => 95,
            ],
            [
                'name' => '28" Large Trolley Suitcase',
                'sku' => 'SU-002',
                'category' => 'Suitcase',
                'description' => 'Expansive family check-in luggage with TSA combination lock.',
                'size' => '28 Inch Check-in',
                'color' => 'Deep Rose Gold',
                'unit' => 'Pcs',
                'wholesale_price' => 3100.00,
                'selling_price' => 5400.00,
                'min_stock_level' => 15,
                'initial_stock' => 60,
            ],
            [
                'name' => 'Anti-theft Crossbody Sling Bag',
                'sku' => 'OB-001',
                'category' => 'Other Bags',
                'description' => 'Chest sling bag with RFID blocking card pockets and hidden zippers.',
                'size' => 'Compact',
                'color' => 'Matte Black',
                'unit' => 'Pcs',
                'wholesale_price' => 350.00,
                'selling_price' => 699.00,
                'min_stock_level' => 40,
                'initial_stock' => 300,
            ],
        ];

        $products = [];
        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category']];
            $initialStock = $pData['initial_stock'];

            $product = Product::firstOrCreate(
                ['sku' => $pData['sku']],
                [
                    'name' => $pData['name'],
                    'category_id' => $cat->id,
                    'description' => $pData['description'],
                    'size' => $pData['size'],
                    'color' => $pData['color'],
                    'unit' => $pData['unit'],
                    'wholesale_price' => $pData['wholesale_price'],
                    'selling_price' => $pData['selling_price'],
                    'min_stock_level' => $pData['min_stock_level'],
                    'current_stock' => $initialStock,
                    'status' => 'active',
                ]
            );

            $products[$pData['sku']] = $product;

            // Record initial opening stock movement if not already recorded
            if ($initialStock > 0 && !StockMovement::where('product_id', $product->id)->where('movement_type', 'opening_stock')->exists()) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'opening_stock',
                    'quantity' => $initialStock,
                    'reference_type' => 'OpeningStock',
                    'reference_id' => $product->id,
                    'date' => Carbon::now()->subMonths(2),
                    'notes' => 'Initial opening inventory setup',
                    'created_by' => $admin->id,
                ]);
            }
        }

        // 5. Customers (5 Distributors, 5 Wholesalers, 3 Retailers, 3 Individuals)
        $customersData = [
            // Distributors (5)
            [
                'name' => 'ABC Bags Distributor', // Key for user demonstration scenario
                'customer_type' => 'distributor',
                'mobile' => '+91 98201 11222',
                'email' => 'orders@abcbags.com',
                'address' => 'Gala #14, Apex Industrial Estate, Kurla West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400070',
                'gstin' => '27ABCDE5555F1Z1',
                'credit_limit' => 200000.00,
                'opening_balance' => 20000.00,
                'status' => 'active',
                'notes' => 'Primary distributor for Central Mumbai & Suburbs. 30 days credit terms.',
            ],
            [
                'name' => 'Apex Bag Distributors',
                'customer_type' => 'distributor',
                'mobile' => '+91 98110 33445',
                'email' => 'sales@apexdist.in',
                'address' => 'Shop 104, Chandni Chowk Market',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'pincode' => '110006',
                'gstin' => '07AAACA1234F1Z8',
                'credit_limit' => 250000.00,
                'opening_balance' => 35000.00,
                'status' => 'active',
                'notes' => 'North India regional bulk distributor.',
            ],
            [
                'name' => 'South Zone Bag Distributing Co.',
                'customer_type' => 'distributor',
                'mobile' => '+91 98450 55667',
                'email' => 'southzone@bagsdist.com',
                'address' => '44/2, Commercial Street Industrial Layout',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pincode' => '560001',
                'gstin' => '29BBBBB4321E1Z3',
                'credit_limit' => 300000.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Karnataka and Tamil Nadu wholesale supply partner.',
            ],
            [
                'name' => 'Gujarat Luggage & Bags Agency',
                'customer_type' => 'distributor',
                'mobile' => '+91 98250 77889',
                'email' => 'gujaratagency@bags.com',
                'address' => 'Plot 88, GIDC Naroda',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '382330',
                'gstin' => '24CCCCC9876D1Z2',
                'credit_limit' => 150000.00,
                'opening_balance' => 15000.00,
                'status' => 'active',
                'notes' => 'Supplies bags across Ahmedabad, Surat, and Vadodara.',
            ],
            [
                'name' => 'Eastern Bags Distribution Hub',
                'customer_type' => 'distributor',
                'mobile' => '+91 98300 99001',
                'email' => 'eastbags@kolkatahub.com',
                'address' => '12, Burrabazar Main Road',
                'city' => 'Kolkata',
                'state' => 'West Bengal',
                'pincode' => '700001',
                'gstin' => '19DDDDD6543C1Z9',
                'credit_limit' => 200000.00,
                'opening_balance' => 25000.00,
                'status' => 'active',
                'notes' => 'Kolkata and North-East distribution network.',
            ],

            // Wholesalers (5)
            [
                'name' => 'Metro Bag Wholesalers',
                'customer_type' => 'wholesaler',
                'mobile' => '+91 97654 11223',
                'email' => 'metro@bagswholesale.in',
                'address' => '102 Laxmi Road',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411030',
                'gstin' => '27EEEEE3210B1Z4',
                'credit_limit' => 100000.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Regular buyer of backpacks and school bags.',
            ],
            [
                'name' => 'National Leather & Bag Wholesale',
                'customer_type' => 'wholesaler',
                'mobile' => '+91 99887 22334',
                'email' => 'nationalbagshyd@gmail.com',
                'address' => '5-9-122 Abids',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'pincode' => '500001',
                'gstin' => '36FFFFF7890A1Z5',
                'credit_limit' => 120000.00,
                'opening_balance' => 18000.00,
                'status' => 'active',
                'notes' => 'Orders laptop and travel bags monthly.',
            ],
            [
                'name' => 'Shree Ganesh Bags Wholesaler',
                'customer_type' => 'wholesaler',
                'mobile' => '+91 98980 44556',
                'email' => 'ganeshbags.surat@yahoo.com',
                'address' => 'Ring Road Textile Market Complex',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395002',
                'gstin' => '24GGGGG5432H1Z6',
                'credit_limit' => 80000.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Tote bags and school bag bulk purchaser.',
            ],
            [
                'name' => 'Crown Bags Wholesale Mart',
                'customer_type' => 'wholesaler',
                'mobile' => '+91 94140 66778',
                'email' => 'crownbags.jaipur@gmail.com',
                'address' => 'Johari Bazar',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302003',
                'gstin' => '08HHHHH8765I1Z7',
                'credit_limit' => 75000.00,
                'opening_balance' => 12000.00,
                'status' => 'active',
                'notes' => 'Tourist duffel and rucksack buyer.',
            ],
            [
                'name' => 'Classic Wholesale Traders',
                'customer_type' => 'wholesaler',
                'mobile' => '+91 98260 88990',
                'email' => 'classic.indore@hotmail.com',
                'address' => 'Rajwada Market',
                'city' => 'Indore',
                'state' => 'Madhya Pradesh',
                'pincode' => '452002',
                'gstin' => '23IIIII4321J1Z8',
                'credit_limit' => 90000.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Bi-weekly regular ordering customer.',
            ],

            // Retailers (3)
            [
                'name' => 'Trendy Luggage Corner',
                'customer_type' => 'retailer',
                'mobile' => '+91 98410 12345',
                'email' => 'trendyluggage@gmail.com',
                'address' => 'Pondy Bazaar, T. Nagar',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'pincode' => '600017',
                'gstin' => '33JJJJJ1234K1Z9',
                'credit_limit' => 50000.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Premium retail showroom.',
            ],
            [
                'name' => 'City Backpacks Retail Store',
                'customer_type' => 'retailer',
                'mobile' => '+91 99350 23456',
                'email' => 'citybackpacks.lko@gmail.com',
                'address' => 'Hazratganj Main Market',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'pincode' => '226001',
                'gstin' => '09KKKKK9876L1Z1',
                'credit_limit' => 40000.00,
                'opening_balance' => 5000.00,
                'status' => 'active',
                'notes' => 'College campus retail outlet.',
            ],
            [
                'name' => 'Modern Bag Station',
                'customer_type' => 'retailer',
                'mobile' => '+91 98150 34567',
                'email' => 'modernbags.chd@gmail.com',
                'address' => 'Sector 17 Plaza',
                'city' => 'Chandigarh',
                'state' => 'Chandigarh',
                'pincode' => '160017',
                'gstin' => '04LLLLL5678M1Z2',
                'credit_limit' => 45000.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Retail outlet for luggage and office bags.',
            ],

            // Individual Customers (3)
            [
                'name' => 'Rahul Sharma',
                'customer_type' => 'individual',
                'mobile' => '+91 98200 45678',
                'email' => 'rahul.sharma88@gmail.com',
                'address' => 'B-402, Sea Breeze Apts, Bandra',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400050',
                'gstin' => null,
                'credit_limit' => 0.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Direct individual customer.',
            ],
            [
                'name' => 'Priya Patel',
                'customer_type' => 'individual',
                'mobile' => '+91 98790 56789',
                'email' => 'priya.patel@gmail.com',
                'address' => '12 Shanti Nagar',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380015',
                'gstin' => null,
                'credit_limit' => 0.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Purchased tote bags for corporate gifting.',
            ],
            [
                'name' => 'Amitabh Sen',
                'customer_type' => 'individual',
                'mobile' => '+91 98310 67890',
                'email' => 'amitabh.sen@rediffmail.com',
                'address' => '78 Southern Avenue',
                'city' => 'Kolkata',
                'state' => 'West Bengal',
                'pincode' => '700029',
                'gstin' => null,
                'credit_limit' => 0.00,
                'opening_balance' => 0.00,
                'status' => 'active',
                'notes' => 'Direct buyer.',
            ],
        ];

        $customers = [];
        foreach ($customersData as $cData) {
            $customer = Customer::firstOrCreate(
                ['name' => $cData['name']],
                $cData
            );
            $customers[$cData['name']] = $customer;

            // If customer has opening balance, create opening ledger entry
            if ($customer->opening_balance > 0 && !LedgerEntry::where('customer_id', $customer->id)->where('reference_type', 'opening_balance')->exists()) {
                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'transaction_date' => Carbon::now()->subMonths(1)->startOfMonth(),
                    'reference_type' => 'opening_balance',
                    'reference_id' => null,
                    'description' => 'Opening Balance',
                    'debit' => $customer->opening_balance,
                    'credit' => 0,
                    'running_balance' => $customer->opening_balance,
                    'created_by' => $admin->id,
                ]);
            }
        }

        // 6. Realistic Historical Orders, Payments, Stock Deductions & Ledgers
        // Let's create orders for Apex Bag Distributors, South Zone, Metro Bag Wholesalers, etc.
        // We will leave ABC Bags Distributor pristine with just opening balance ₹20,000 so the user can execute the exact Demo Scenario!
        $demoOrders = [
            [
                'customer' => 'Apex Bag Distributors',
                'date' => Carbon::now()->subDays(18),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'items' => [
                    ['sku' => 'BP-001', 'qty' => 40, 'price' => 650.00, 'discount' => 500.00],
                    ['sku' => 'BP-002', 'qty' => 20, 'price' => 950.00, 'discount' => 0.00],
                ],
                'discount' => 500.00,
                'tax' => 2000.00,
                'payments' => [
                    ['amount' => 46000.00, 'method' => 'bank_transfer', 'ref' => 'NEFT-AXIS-99214', 'days_ago' => 15],
                ],
            ],
            [
                'customer' => 'Apex Bag Distributors',
                'date' => Carbon::now()->subDays(8),
                'status' => 'dispatched',
                'payment_status' => 'partially_paid',
                'items' => [
                    ['sku' => 'RK-001', 'qty' => 25, 'price' => 1450.00, 'discount' => 0.00],
                    ['sku' => 'TR-001', 'qty' => 30, 'price' => 750.00, 'discount' => 0.00],
                ],
                'discount' => 750.00,
                'tax' => 2500.00,
                'payments' => [
                    ['amount' => 30000.00, 'method' => 'bank_transfer', 'ref' => 'RTGS-998822', 'days_ago' => 6],
                ],
            ],
            [
                'customer' => 'South Zone Bag Distributing Co.',
                'date' => Carbon::now()->subDays(12),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'items' => [
                    ['sku' => 'TB-001', 'qty' => 100, 'price' => 180.00, 'discount' => 0.00],
                    ['sku' => 'SB-001', 'qty' => 50, 'price' => 520.00, 'discount' => 0.00],
                ],
                'discount' => 1000.00,
                'tax' => 1500.00,
                'payments' => [
                    ['amount' => 44500.00, 'method' => 'bank_transfer', 'ref' => 'HDFC-IMPS-8871', 'days_ago' => 10],
                ],
            ],
            [
                'customer' => 'South Zone Bag Distributing Co.',
                'date' => Carbon::now()->subDays(3),
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'items' => [
                    ['sku' => 'SU-001', 'qty' => 15, 'price' => 2200.00, 'discount' => 0.00],
                    ['sku' => 'LP-001', 'qty' => 20, 'price' => 850.00, 'discount' => 0.00],
                ],
                'discount' => 0.00,
                'tax' => 2500.00,
                'payments' => [],
            ],
            [
                'customer' => 'Metro Bag Wholesalers',
                'date' => Carbon::now()->subDays(14),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'items' => [
                    ['sku' => 'SB-001', 'qty' => 30, 'price' => 520.00, 'discount' => 0.00],
                    ['sku' => 'SB-002', 'qty' => 20, 'price' => 680.00, 'discount' => 0.00],
                ],
                'discount' => 0.00,
                'tax' => 1200.00,
                'payments' => [
                    ['amount' => 30400.00, 'method' => 'upi', 'ref' => 'UPI/GPay/44910293', 'days_ago' => 12],
                ],
            ],
            [
                'customer' => 'Metro Bag Wholesalers',
                'date' => Carbon::now()->subDays(2),
                'status' => 'packed',
                'payment_status' => 'partially_paid',
                'items' => [
                    ['sku' => 'BP-001', 'qty' => 25, 'price' => 650.00, 'discount' => 0.00],
                    ['sku' => 'OB-001', 'qty' => 40, 'price' => 350.00, 'discount' => 0.00],
                ],
                'discount' => 500.00,
                'tax' => 1500.00,
                'payments' => [
                    ['amount' => 15000.00, 'method' => 'upi', 'ref' => 'UPI/PhonePe/99281', 'days_ago' => 1],
                ],
            ],
            [
                'customer' => 'National Leather & Bag Wholesale',
                'date' => Carbon::now()->subDays(5),
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'items' => [
                    ['sku' => 'LP-002', 'qty' => 15, 'price' => 1050.00, 'discount' => 0.00],
                    ['sku' => 'TR-002', 'qty' => 20, 'price' => 620.00, 'discount' => 0.00],
                ],
                'discount' => 0.00,
                'tax' => 1400.00,
                'payments' => [],
            ],
            [
                'customer' => 'City Backpacks Retail Store',
                'date' => Carbon::now()->subDays(1),
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'items' => [
                    ['sku' => 'BP-001', 'qty' => 15, 'price' => 650.00, 'discount' => 0.00],
                ],
                'discount' => 0.00,
                'tax' => 487.50,
                'payments' => [],
            ],
            [
                'customer' => 'Rahul Sharma',
                'date' => Carbon::today(),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'items' => [
                    ['sku' => 'BP-002', 'qty' => 1, 'price' => 1650.00, 'discount' => 0.00], // Individual customer selling price
                ],
                'discount' => 0.00,
                'tax' => 150.00,
                'payments' => [
                    ['amount' => 1800.00, 'method' => 'cash', 'ref' => 'CASH-REC', 'days_ago' => 0],
                ],
            ],
        ];

        $orderNumberSeq = 1;
        $paymentNumberSeq = 1;
        $year = date('Y');

        foreach ($demoOrders as $oData) {
            $customer = $customers[$oData['customer']];
            $orderNumber = sprintf('ORD-%s-%04d', $year, $orderNumberSeq++);

            // Compute subtotal
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($oData['items'] as $itemData) {
                $prod = $products[$itemData['sku']];
                $lineSub = ($itemData['qty'] * $itemData['price']) - $itemData['discount'];
                $subtotal += ($itemData['qty'] * $itemData['price']);

                $itemsToCreate[] = [
                    'product_id' => $prod->id,
                    'quantity' => $itemData['qty'],
                    'unit_price' => $itemData['price'],
                    'discount' => $itemData['discount'],
                    'tax' => 0,
                    'subtotal' => $lineSub,
                ];
            }

            $grandTotal = $subtotal - $oData['discount'] + $oData['tax'];

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'order_date' => $oData['date'],
                'status' => $oData['status'],
                'payment_status' => $oData['payment_status'],
                'subtotal' => $subtotal,
                'discount' => $oData['discount'],
                'tax' => $oData['tax'],
                'grand_total' => $grandTotal,
                'paid_amount' => 0,
                'notes' => 'Historical wholesale booking',
            ]);

            foreach ($itemsToCreate as $it) {
                $order->items()->create($it);
            }

            // Deduct stock for confirmed/delivered/packed orders
            if ($order->status !== 'draft' && $order->status !== 'cancelled') {
                foreach ($order->items as $it) {
                    $prod = Product::find($it->product_id);
                    StockMovement::create([
                        'product_id' => $prod->id,
                        'movement_type' => 'sale',
                        'quantity' => -1 * abs($it->quantity),
                        'reference_type' => 'Order',
                        'reference_id' => $order->id,
                        'date' => $order->order_date,
                        'notes' => "Sale deduction for Order #{$order->order_number}",
                        'created_by' => $admin->id,
                    ]);
                }

                // Debit Customer Ledger
                $latestLedger = LedgerEntry::where('customer_id', $customer->id)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $prevBal = $latestLedger ? (float) $latestLedger->running_balance : (float) $customer->opening_balance;
                $newBal = $prevBal + $order->grand_total;

                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'transaction_date' => $order->order_date,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'description' => "Order #{$order->order_number}",
                    'debit' => $order->grand_total,
                    'credit' => 0,
                    'running_balance' => $newBal,
                    'created_by' => $admin->id,
                ]);
            }

            // Process payments if any
            $totalPaidForOrder = 0;
            foreach ($oData['payments'] as $payData) {
                $payNumber = sprintf('PAY-%s-%04d', $year, $paymentNumberSeq++);
                $payDate = Carbon::now()->subDays($payData['days_ago']);

                $payment = Payment::create([
                    'payment_number' => $payNumber,
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'amount' => $payData['amount'],
                    'payment_date' => $payDate,
                    'payment_method' => $payData['method'],
                    'reference_number' => $payData['ref'],
                    'notes' => "Payment received against Order #{$order->order_number}",
                    'created_by' => $admin->id,
                ]);

                $totalPaidForOrder += $payData['amount'];

                // Credit Customer Ledger
                $latestLedger = LedgerEntry::where('customer_id', $customer->id)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $prevBal = $latestLedger ? (float) $latestLedger->running_balance : (float) $customer->opening_balance;
                $newBal = $prevBal - $payData['amount'];

                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'transaction_date' => $payDate,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'description' => "Payment #{$payment->payment_number} (Order #{$order->order_number})",
                    'debit' => 0,
                    'credit' => $payData['amount'],
                    'running_balance' => $newBal,
                    'created_by' => $admin->id,
                ]);
            }

            $order->paid_amount = $totalPaidForOrder;
            $order->save();
        }
    }
}
