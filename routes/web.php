<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.home')->name('home');

Volt::route('produits', 'pages.products.index')->name('products.index');
Volt::route('produits/{product:slug}', 'pages.products.show')->name('products.show');

Volt::route('vendeurs', 'pages.sellers.index')->name('sellers.index');
Volt::route('vendeurs/{shop_slug}', 'pages.sellers.show')->name('sellers.show');

Volt::route('panier', 'pages.cart.show')->name('cart.show');

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

Route::middleware(['auth', 'role:vendeur'])->prefix('vendeur')->name('vendor.')->group(function () {
    Volt::route('/', 'pages.vendor.dashboard')->name('dashboard');
    Volt::route('produits', 'pages.vendor.products.index')->name('products.index');
    Volt::route('produits/creer', 'pages.vendor.products.form')->name('products.create');
    Volt::route('produits/{product}/modifier', 'pages.vendor.products.form')->name('products.edit');
    Volt::route('commandes', 'pages.vendor.orders')->name('orders');
});

require __DIR__.'/auth.php';
