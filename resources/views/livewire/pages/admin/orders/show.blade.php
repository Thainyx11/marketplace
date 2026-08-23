<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Order $order;

    public string $status = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items.product', 'items.seller', 'buyer', 'payment']);
        $this->status = $order->status;
    }

    public function overrideStatus(): void
    {
        $this->order->update(['status' => $this->status]);
        session()->flash('status', __('Statut de la commande mis à jour (intervention manuelle).'));
    }

    public function with(): array
    {
        return [];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    @if (session('status'))
        <div class="bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg p-4 text-sm mb-6">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Commande #:id', ['id' => $order->id]) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Acheteur') }} : {{ $order->buyer->name }} ({{ $order->buyer->email }})</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ __('Livraison') }} : {{ $order->shipping_address }}</p>
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
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'expediee' => 'Expédiée', 'livree' => 'Livrée'][$item->status] }}</span>
                </div>
            @endforeach
        </div>

        <p class="font-bold text-lg text-gray-900 dark:text-gray-100 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            {{ __('Total') }} : {{ number_format($order->total, 2, ',', ' ') }} €
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mt-6">
        <p class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Intervention (litige)') }}</p>
        <div class="flex items-center gap-3">
            <select wire:model="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                <option value="en_attente">{{ __('En attente') }}</option>
                <option value="acceptee">{{ __('Acceptée') }}</option>
                <option value="expediee">{{ __('Expédiée') }}</option>
                <option value="livree">{{ __('Livrée') }}</option>
            </select>
            <button type="button" wire:click="overrideStatus" class="text-sm bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                {{ __('Forcer ce statut') }}
            </button>
        </div>
    </div>
</div>
