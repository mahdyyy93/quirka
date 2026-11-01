<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\AdminOrderController as PrintletAdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Admin\PricingLimits;
use App\Livewire\Admin\RoleManager;
use App\Livewire\Artist\PricingSettings as ArtistPricingSettings;
use Illuminate\Support\Facades\Route;

// Public storefront
Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/products/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

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
    Route::get('/artist/pricing', ArtistPricingSettings::class)->name('artist.pricing')->middleware('permission:artist.pricing');
    Route::get('/artist/wallet', \App\Livewire\Artist\WalletDashboard::class)->name('artist.wallet');
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
    Route::post('/order/{order}/instapay-reference', [CheckoutController::class, 'updatePaymentReference'])->name('order.instapay-reference');

    // Customer order history — page + JSON data endpoint
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('my-orders.index');
    Route::get('/my-orders/data', [OrderController::class, 'index'])->name('my-orders.data');
    Route::get('/my-orders/{order}', [OrderController::class, 'show'])->name('my-orders.show');

    // Legacy order endpoint kept for compatibility
    Route::post('/admin/orders', [OrderController::class, 'store']);
});

// Admin & Management routes
Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('products.index'))->name('dashboard');
    Route::get('/products', fn () => view('products'))->name('products.index')->middleware('permission:products.index');
    Route::get('/orders', fn () => view('orders'))->name('orders.index')->middleware('permission:orders.index');
    Route::get('/customers', fn () => view('customers'))->name('customers.index')->middleware('permission:customers.index');
    Route::get('/roles', RoleManager::class)->name('roles.index')->middleware('permission:roles.index');
    Route::get('/pricing-limits', PricingLimits::class)->name('pricing-limits.index')->middleware('permission:pricing-limits.index');
    Route::get('/revenue-report', [RevenueReportController::class, 'index'])->name('revenue-report.index')->middleware('permission:revenue-report.index');
    Route::get('/wallets', \App\Livewire\Admin\WalletManager::class)->name('wallets.index');

    Route::get('/admin/products', [ProductController::class, 'index'])->name('products.list')->middleware('permission:products.list');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:products.store');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:products.destroy');
    Route::patch('/admin/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle-availability')->middleware('permission:products.toggle-availability');

    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('orders.list')->middleware('permission:orders.list');
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('customers.list')->middleware('permission:customers.list');
    Route::patch('/admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status')->middleware('permission:orders.update-status');
    Route::post('/admin/orders/{order}/verify-cod', [PrintletAdminOrderController::class, 'verifyCod'])->name('orders.verify-cod')->middleware('permission:orders.verify-cod');
    Route::post('/admin/orders/{order}/verify-instapay', [PrintletAdminOrderController::class, 'verifyInstapay'])->name('orders.verify-instapay')->middleware('permission:orders.verify-instapay');
});

require __DIR__.'/auth.php';
