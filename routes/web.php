<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.home')->name('home');

Volt::route('produits', 'pages.products.index')->name('products.index');
Volt::route('produits/{product:slug}', 'pages.products.show')->name('products.show');

Volt::route('vendeurs', 'pages.sellers.index')->name('sellers.index');
Volt::route('vendeurs/{shop_slug}', 'pages.sellers.show')->name('sellers.show');

Volt::route('panier', 'pages.cart.show')->name('cart.show');

Route::get('mentions-legales', function () {
    return view('legal-notice', ['content' => Setting::get('legal_notice', '')]);
})->name('legal-notice');

Route::post('webhook/stripe', [StripeWebhookController::class, 'handle'])->name('webhook.stripe');

Route::middleware(['auth'])->group(function () {
    Route::get('commande', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('commande', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('commande/succes', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('commandes', [OrderController::class, 'index'])->name('orders.index');
    Route::get('commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('commandes/{order}/facture', [OrderController::class, 'invoice'])->name('orders.invoice');

    Volt::route('messages', 'pages.messages.index')->name('messages.index');
    Volt::route('messages/{product}/{user}', 'pages.messages.show')->name('messages.show');

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
});

// Admins can also own and manage a baseline product catalog through this
// same UI (see ProductPolicy::create) so the marketplace is never empty.
Route::middleware(['auth', 'role:vendeur|admin'])->prefix('vendeur')->name('vendor.')->group(function () {
    Volt::route('/', 'pages.vendor.dashboard')->name('dashboard');
    Volt::route('produits', 'pages.vendor.products.index')->name('products.index');
    Volt::route('produits/creer', 'pages.vendor.products.form')->name('products.create');
    Volt::route('produits/{product}/modifier', 'pages.vendor.products.form')->name('products.edit');
    Volt::route('commandes', 'pages.vendor.orders')->name('orders');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/', 'pages.admin.dashboard')->name('dashboard');
    Volt::route('utilisateurs', 'pages.admin.users.index')->name('users.index');
    Volt::route('vendeurs', 'pages.admin.vendors')->name('vendors');
    Volt::route('categories', 'pages.admin.categories')->name('categories');
    Volt::route('produits', 'pages.admin.products')->name('products');
    Volt::route('commandes', 'pages.admin.orders.index')->name('orders.index');
    Volt::route('commandes/{order}', 'pages.admin.orders.show')->name('orders.show');
    Volt::route('avis', 'pages.admin.reviews')->name('reviews');
    Volt::route('signalements', 'pages.admin.message-reports')->name('message-reports');
    Volt::route('codes-promo', 'pages.admin.promo-codes')->name('promo-codes');
    Volt::route('parametres', 'pages.admin.settings')->name('settings');
});

require __DIR__.'/auth.php';
