<?php

use App\Models\Order;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Order $order;

    public string $status = '';

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->order = $order->load(['items.product', 'items.seller', 'buyer', 'payment']);
        $this->status = $order->status;
    }

    public function overrideStatus(): void
    {
        Gate::authorize('update', $this->order);

        $this->order->update(['status' => $this->status]);
        session()->flash('status', __('Statut de la commande mis à jour (intervention manuelle).'));
    }

    public function with(): array
    {
        return [];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
        <p class="font-bold text-gray-900 dark:text-gray-100">{{ __('Commande #:id', ['id' => $order->id]) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Acheteur') }} : {{ $order->buyer->name }} ({{ $order->buyer->email }})</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ __('Livraison') }} ({{ $order->shipping_method === 'express' ? __('express') : __('standard') }}) : {{ $order->shipping_address }}</p>
        @if ($order->payment)
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Paiement Stripe') }} : {{ $order->payment->stripe_id }} ({{ $order->payment->status }})</p>
        @endif

        <div class="divide-y divide-gray-100 dark:divide-gray-700 mt-4">
            @foreach ($order->items as $item)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-gray-900 dark:text-gray-100">{{ $item->product->title }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }} × {{ number_format($item->unit_price, 2, ',', ' ') }} € — {{ __('vendeur') }} : {{ $item->seller->name }}</p>
                    </div>
                    <x-order-status-badge :status="$item->status" />
                </div>
            @endforeach
        </div>

        <p class="font-extrabold text-lg text-gray-900 dark:text-gray-100 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            {{ __('Total') }} : {{ number_format($order->total, 2, ',', ' ') }} €
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mt-6">
        <p class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Intervention (litige)') }}</p>
        <div class="flex items-center gap-3">
            <select wire:model="status" class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="en_attente">{{ __('En attente') }}</option>
                <option value="acceptee">{{ __('Acceptée') }}</option>
                <option value="expediee">{{ __('Expédiée') }}</option>
                <option value="livree">{{ __('Livrée') }}</option>
            </select>
            <button type="button" wire:click="overrideStatus" class="text-sm font-semibold bg-gray-900 hover:bg-gray-700 dark:bg-gray-100 dark:hover:bg-white dark:text-gray-900 text-white px-4 py-2 rounded-full transition">
                {{ __('Forcer ce statut') }}
            </button>
        </div>
    </div>
</div>
