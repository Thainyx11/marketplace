<?php

use App\Models\OrderItem;
use Illuminate\Support\Facades\Gate;
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
        return [
            'items' => auth()->user()->saleItems()
                ->with(['product', 'order.buyer'])
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Commandes reçues') }}</h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($items as $item)
            <div class="flex items-center gap-4 p-4" wire:key="order-item-{{ $item->id }}">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $item->product->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Commande #:id', ['id' => $item->order_id]) }} · {{ $item->order->buyer->name }} ·
                        {{ $item->quantity }} × {{ number_format($item->unit_price, 2, ',', ' ') }} €
                    </p>
                </div>

                <span @class([
                    'text-xs px-2 py-1 rounded-full font-medium shrink-0',
                    'bg-amber-100 text-amber-800' => $item->status === 'en_attente',
                    'bg-blue-100 text-blue-800' => $item->status === 'acceptee',
                    'bg-purple-100 text-purple-800' => $item->status === 'expediee',
                    'bg-green-100 text-green-800' => $item->status === 'livree',
                ])>
                    {{ ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'expediee' => 'Expédiée', 'livree' => 'Livrée'][$item->status] }}
                </span>

                @if (isset(self::NEXT_STATUS[$item->status]))
                    <button type="button" wire:click="advance({{ $item->id }})"
                            class="text-sm bg-gray-800 hover:bg-gray-700 text-white px-3 py-1.5 rounded-lg shrink-0">
                        {{ ['en_attente' => 'Accepter', 'acceptee' => 'Marquer expédiée', 'expediee' => 'Marquer livrée'][$item->status] }}
                    </button>
                @endif
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __("Aucune commande reçue pour l'instant.") }}</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
</div>
