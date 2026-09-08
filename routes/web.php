<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\AdminOrderController as PrintletAdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// Public storefront
Route::get('/', [ShopController::class, 'index'])->name('shop.index');

// Public legal & policy pages
Route::get('/shipping', fn () => view('pages.shipping'))->name('legal.shipping');
Route::get('/returns', fn () => view('pages.returns'))->name('legal.returns');
Route::get('/terms', fn () => view('pages.terms'))->name('legal.terms');
Route::get('/privacy', fn () => view('pages.privacy'))->name('legal.privacy');

// Printlet Webhook endpoint (CSRF-exempt)
Route::post('/webhooks/printlet', [WebhookController::class, 'handle'])->name('webhooks.printlet');

// Profile (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Guest-accessible cart routes (session-based, no auth required)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

// Authenticated customer routes
Route::middleware('auth')->group(function () {
    // Checkout — POST processes the order
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/order/{order}/confirmation', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

    // Customer order history — page + JSON data endpoint
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('my-orders.index');
    Route::get('/my-orders/data', [OrderController::class, 'index'])->name('my-orders.data');
    Route::get('/my-orders/{order}', [OrderController::class, 'show'])->name('my-orders.show');

    // Legacy order endpoint kept for compatibility
    Route::post('/admin/orders', [OrderController::class, 'store']);
});

// Admin-only routes
Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/products', fn () => view('products'))->name('dashboard');
    Route::get('/suppliers', fn () => view('suppliers'))->name('suppliers.index');
    Route::get('/orders', fn () => view('orders'))->name('orders.index');
    Route::get('/customers', fn () => view('customers'))->name('customers.index');

    Route::get('/admin/products', [ProductController::class, 'index']);
    Route::post('/admin/products', [ProductController::class, 'store']);
    Route::put('/admin/products/{product}', [ProductController::class, 'update']);
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy']);
    Route::patch('/admin/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability']);

    Route::get('/admin/suppliers', [SupplierController::class, 'index']);
    Route::post('/admin/suppliers', [SupplierController::class, 'store']);
    Route::put('/admin/suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::delete('/admin/suppliers/{supplier}', [SupplierController::class, 'destroy']);
    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    Route::get('/admin/customers', [CustomerController::class, 'index']);
    Route::patch('/admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    Route::post('/admin/orders/{order}/verify-cod', [PrintletAdminOrderController::class, 'verifyCod'])->name('admin.orders.verify-cod');
});

require __DIR__.'/auth.php';
