<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OutstandingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated ERP / Wholesale Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', ProductController::class)->except(['show']);

    // Categories
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Inventory & Stock Movements
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Orders
    Route::resource('orders', OrderController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Outstanding Receivables
    Route::get('outstanding', [OutstandingController::class, 'index'])->name('outstanding.index');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        Route::get('/outstanding', [ReportController::class, 'outstanding'])->name('outstanding');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
    });

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
