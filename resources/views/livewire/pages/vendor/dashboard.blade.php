<?php

use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $seller = auth()->user();

        $items = $seller->saleItems()->with('order')->get();

        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());

        $revenueByMonth = $months->mapWithKeys(function (Carbon $month) use ($items) {
            $label = $month->translatedFormat('M Y');
            $sum = $items->filter(fn ($item) => $item->order->created_at->isSameMonth($month) && $item->order->created_at->isSameYear($month))
                ->sum(fn ($item) => $item->unit_price * $item->quantity);

            return [$label => round((float) $sum, 2)];
        });

        return [
            'totalSales' => $items->count(),
            'totalRevenue' => $items->sum(fn ($item) => $item->unit_price * $item->quantity),
            'productsCount' => $seller->products()->count(),
            'chartLabels' => $revenueByMonth->keys()->all(),
            'chartData' => $revenueByMonth->values()->all(),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Tableau de bord vendeur') }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Chiffre d\'affaires') }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totalRevenue, 2, ',', ' ') }} €</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Ventes') }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalSales }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Produits en vente') }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $productsCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('vendor.products.index') }}" wire:navigate class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
            <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Mes produits') }}</p>
        </a>
        <a href="{{ route('vendor.orders') }}" wire:navigate class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
            <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Commandes reçues') }}</p>
        </a>
        <a href="{{ route('messages.index') }}" wire:navigate class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
            <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Messages') }}</p>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5"
         x-data="{
            init() {
                new Chart(this.$refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @js($chartLabels),
                        datasets: [{ label: @js(__('Chiffre d\'affaires (€)')), data: @js($chartData), backgroundColor: '#6366f1' }],
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } },
                });
            }
         }">
        <p class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Chiffre d\'affaires mensuel') }}</p>
        <canvas x-ref="canvas" height="90"></canvas>
    </div>
</div>
