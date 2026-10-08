<?php

use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Stored uploads. On Vercel the public disk writes to the media table
// (FILESYSTEM_PUBLIC_DRIVER=db); this is the public reader, so it stays
// outside every auth group — admins view the same images as guests.
Route::get('/media/{path}', function (string $path) {
    try {
        $disk = Storage::disk('public');

        if (str_contains($path, '..') || ! $disk->exists($path)) {
            abort(404);
        }

        return response($disk->get($path), 200, [
            'Content-Type' => $disk->mimeType($path),
            // store() names files with a content hash, so the URL is immutable.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    } catch (Throwable) {
        abort(404);
    }
})->where('path', '.*');

// The storefront is a customer surface: an admin manages the shop from /admin
// and gets a 403 here, so these links are never rendered for them at all.
Route::middleware('customer')->group(function () {
    Route::get('/', [ProductController::class, 'home'])->name('home');
    Route::get('/products', [ProductController::class, 'index'])->name('products.catalog');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});

Route::middleware('auth')->group(function () {
    // One redirect point instead of six: every auth controller already aims at
    // route('dashboard'), so an admin bounces to /admin here rather than being
    // patched into each of them.
    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : view('dashboard');
    })->middleware('verified')->name('dashboard');
});

// Everything an admin does NOT have. Admins run the store from /admin —
// including their own account screen — so this group is a 403 for them.
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart (session-backed)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');

    Route::resource('categories', AdminCategoryController::class);
    Route::resource('products', AdminProductController::class);

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/settings/payment', [PaymentSettingController::class, 'edit'])->name('settings.payment.edit');
    Route::patch('/settings/payment', [PaymentSettingController::class, 'update'])->name('settings.payment.update');

    Route::get('/settings/account', [AdminAccountController::class, 'edit'])->name('settings.account.edit');
    Route::patch('/settings/account', [AdminAccountController::class, 'update'])->name('settings.account.update');
});

require __DIR__.'/auth.php';
