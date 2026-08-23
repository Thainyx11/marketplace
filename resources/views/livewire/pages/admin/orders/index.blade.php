<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return ['orders' => Order::with(['buyer', 'items'])->latest()->paginate(15)];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @foreach ($orders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Commande #:id', ['id' => $order->id]) }} — {{ $order->buyer->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->items->count() }} {{ __('article(s)') }}</p>
                </div>
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($order->total, 2, ',', ' ') }} €</span>
                <span @class([
                    'text-xs px-2 py-1 rounded-full font-medium',
                    'bg-amber-100 text-amber-800' => $order->status === 'en_attente',
                    'bg-blue-100 text-blue-800' => $order->status === 'acceptee',
                    'bg-purple-100 text-purple-800' => $order->status === 'expediee',
                    'bg-green-100 text-green-800' => $order->status === 'livree',
                ])>
                    {{ ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'expediee' => 'Expédiée', 'livree' => 'Livrée'][$order->status] }}
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
