<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileDataExportController;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.home')->name('home');

// FIX: no sitemap existed at all — a real gap for a catalog site meant to be
// found by search engines. Lists every URL a crawler should actually index:
// static pages, every active product, every approved shop. robots.txt was a
// static public/ file with no Sitemap: line (and couldn't have one — it needs
// an absolute URL, which a static file can't know per-environment), so it's
// now a route too, built from the same url() helper as the sitemap itself.
Route::get('sitemap.xml', function () {
    $urls = collect([
        ['loc' => url('/'), 'priority' => '1.0'],
        ['loc' => route('products.index'), 'priority' => '0.9'],
        ['loc' => route('sellers.index'), 'priority' => '0.7'],
        ['loc' => route('wanted.index'), 'priority' => '0.5'],
    ]);

    $urls = $urls
        ->concat(Product::active()->select('id', 'slug', 'updated_at')->get()->map(fn (Product $p) => [
            'loc' => route('products.show', $p->slug),
            'lastmod' => $p->updated_at->toAtomString(),
            'priority' => '0.8',
        ]))
        ->concat(User::whereIn('role', ['vendeur', 'admin'])
            ->where('is_approved', true)->where('is_active', true)->whereNotNull('shop_slug')
            ->get()->map(fn (User $u) => [
                'loc' => route('sellers.show', $u->shop_slug),
                'priority' => '0.6',
            ]));

    // FIX: the XML prolog used to live inside sitemap.blade.php and 500'd in
    // production only ("syntax error, unexpected identifier 'version'").
    // Blade's own compiler gets confused scanning the raw template text for
    // an opening-tag-like sequence before it becomes a string literal,
    // reproducibly only when short_open_tag is on (it is here, not on the
    // local/CI PHP CLI the test suite runs under). Building it in plain PHP
    // here instead means Blade's compiler never sees it at all — confirmed
    // safe directly on production via tinker before this fix was written.
    // (A single-line "//" comment ends early at a literal closing-tag
    // sequence — the exact two characters XML's own prolog closes with —
    // which is why this note stops short of spelling that sequence out.)
    $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>'."\n";

    return response($xmlDeclaration.view('sitemap', ['urls' => $urls])->render())
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('robots.txt', function () {
    return response("User-agent: *\nDisallow: /admin\nDisallow: /login\nDisallow: /register\nSitemap: ".route('sitemap')."\n")
        ->header('Content-Type', 'text/plain');
});

Volt::route('produits', 'pages.products.index')->name('products.index');
Volt::route('produits/{product:slug}', 'pages.products.show')->name('products.show');

Volt::route('vendeurs', 'pages.sellers.index')->name('sellers.index');
Volt::route('vendeurs/{shop_slug}', 'pages.sellers.show')->name('sellers.show');

// 'nouvelle' must be declared before the {wantedItem} wildcard below, or the
// wildcard route would swallow it as if it were an item id.
Volt::route('recherches/nouvelle', 'pages.wanted.create')->middleware('auth')->name('wanted.create');
Volt::route('recherches', 'pages.wanted.index')->name('wanted.index');
Volt::route('recherches/{wantedItem}', 'pages.wanted.show')->name('wanted.show');

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

    Volt::route('favoris', 'pages.wishlist.index')->name('wishlist.index');

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::get('profil/export-donnees', ProfileDataExportController::class)->name('profile.export');
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
