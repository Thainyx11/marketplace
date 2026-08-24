<?php

use App\Models\OrderItem;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public const NEXT_STATUS = [
        'en_attente' => 'acceptee',
        'acceptee' => 'expediee',
        'expediee' => 'livree',
    ];

    public function advance(OrderItem $orderItem): void
    {
        abort_unless($orderItem->seller_id === auth()->id(), 403);

        $next = self::NEXT_STATUS[$orderItem->status] ?? null;

        if ($next) {
            $orderItem->update(['status' => $next]);
            $orderItem->order->recomputeStatus();
        }
    }

    public function with(): array
    {
        $commissionRate = (float) Setting::get('commission_rate', 5);
        $allItems = auth()->user()->saleItems()->get();

        return [
            'items' => auth()->user()->saleItems()
                ->with(['product', 'order.buyer'])
                ->latest()
                ->paginate(15),
            'commissionRate' => $commissionRate,
            'totalNetPayout' => $allItems->sum(fn (OrderItem $item) => $item->unit_price * $item->quantity * (1 - $commissionRate / 100)),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Commandes reçues') }}</h1>
        <div class="bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl px-4 py-2">
            <span class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('Net total (après commission :rate%)', ['rate' => rtrim(rtrim(number_format($commissionRate, 1, ',', ' '), '0'), ',')]) }}</span>
            <p class="font-extrabold text-emerald-700 dark:text-emerald-300">{{ number_format($totalNetPayout, 2, ',', ' ') }} €</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($items as $item)
            @php
                $gross = $item->unit_price * $item->quantity;
                $net = $gross * (1 - $commissionRate / 100);
            @endphp
            <div class="flex items-center gap-4 p-4" wire:key="order-item-{{ $item->id }}">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $item->product->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Commande #:id', ['id' => $item->order_id]) }} · {{ $item->order->buyer->name }} ·
                        {{ $item->quantity }} × {{ number_format($item->unit_price, 2, ',', ' ') }} €
                    </p>
                </div>

                <div class="text-right shrink-0">
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($gross, 2, ',', ' ') }} €</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ __('net : :amount €', ['amount' => number_format($net, 2, ',', ' ')]) }}</p>
                </div>

                <x-order-status-badge :status="$item->status" class="shrink-0" />

                @if (isset(self::NEXT_STATUS[$item->status]))
                    <button type="button" wire:click="advance({{ $item->id }})"
                            class="text-sm font-semibold bg-gray-900 hover:bg-gray-700 dark:bg-gray-100 dark:hover:bg-white dark:text-gray-900 text-white px-3.5 py-1.5 rounded-full shrink-0 transition">
                        {{ ['en_attente' => 'Accepter', 'acceptee' => 'Marquer expédiée', 'expediee' => 'Marquer livrée'][$item->status] }}
                    </button>
                @endif
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="text-5xl mb-3">📬</div>
                <p class="text-gray-500 dark:text-gray-400">{{ __("Aucune commande reçue pour l'instant.") }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
</div>
