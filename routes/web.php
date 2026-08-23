<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.home')->name('home');

Volt::route('produits', 'pages.products.index')->name('products.index');
Volt::route('produits/{product:slug}', 'pages.products.show')->name('products.show');

Volt::route('vendeurs', 'pages.sellers.index')->name('sellers.index');
Volt::route('vendeurs/{shop_slug}', 'pages.sellers.show')->name('sellers.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
