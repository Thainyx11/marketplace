<?php

use App\Models\MessageReport;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'totalOrders' => Order::count(),
            'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'totalCommission' => Payment::where('status', 'paid')->sum('commission'),
            'totalUsers' => User::count(),
            'totalVendors' => User::where('role', 'vendeur')->where('is_approved', true)->count(),
            'pendingVendors' => User::where('role', 'vendeur')->where('is_approved', false)->count(),
            'totalProducts' => Product::count(),
            'pendingReports' => MessageReport::where('status', 'pending')->count(),
        ];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    @if ($pendingVendors > 0)
        <a href="{{ route('admin.vendors') }}" wire:navigate class="block bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-2xl p-4 text-sm mb-4 font-medium">
            {{ __(':count vendeur(s) en attente de validation →', ['count' => $pendingVendors]) }}
        </a>
    @endif

    @if ($pendingReports > 0)
        <a href="{{ route('admin.message-reports') }}" wire:navigate class="block bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-2xl p-4 text-sm mb-6 font-medium">
            {{ __(':count message(s) signalé(s) à traiter →', ['count' => $pendingReports]) }}
        </a>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <span class="grid place-items-center h-11 w-11 rounded-xl bg-violet-100 dark:bg-violet-900/50 text-xl shrink-0">📦</span>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Commandes') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $totalOrders }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <span class="grid place-items-center h-11 w-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-xl shrink-0">💶</span>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Revenus (commission)') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ number_format($totalCommission, 2, ',', ' ') }} €</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <span class="grid place-items-center h-11 w-11 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-xl shrink-0">📈</span>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Volume total') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ number_format($totalRevenue, 2, ',', ' ') }} €</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <span class="grid place-items-center h-11 w-11 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-xl shrink-0">👥</span>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Utilisateurs') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <span class="grid place-items-center h-11 w-11 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-900/50 text-xl shrink-0">🏪</span>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Vendeurs actifs') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $totalVendors }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <span class="grid place-items-center h-11 w-11 rounded-xl bg-gray-100 dark:bg-gray-700 text-xl shrink-0">🏷️</span>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Produits') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $totalProducts }}</p>
            </div>
        </div>
    </div>
</div>
