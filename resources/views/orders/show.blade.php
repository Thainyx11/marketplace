<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Commande #:id', ['id' => $order->id]) }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 whitespace-pre-line">{{ __('Livraison') }} : {{ $order->shipping_address }}</p>
                    </div>

                    @if ($order->payment?->status === 'paid')
                        <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 underline">
                            {{ __('Télécharger la facture') }}
                        </a>
                    @endif
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($order->items as $item)
                        <div class="py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('products.show', $item->product->slug) }}" class="font-medium text-gray-900 dark:text-gray-100 hover:underline">
                                        {{ $item->product->title }}
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }} × {{ number_format($item->unit_price, 2, ',', ' ') }} € — {{ __('vendu par') }} {{ $item->seller->shop_name ?? $item->seller->name }}</p>
                                </div>
                                <x-order-status-badge :status="$item->status" class="shrink-0" />
                            </div>

                            @if ($item->status === 'livree' && auth()->id() === $order->buyer_id)
                                <livewire:components.leave-review :order="$order" :order-item="$item" :key="'review-'.$item->id" />
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-200 dark:border-gray-700">
                    @if ($order->discount_amount > 0)
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Remise appliquée') }} : -{{ number_format($order->discount_amount, 2, ',', ' ') }} €</span>
                    @else
                        <span></span>
                    @endif
                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ __('Total') }} : {{ number_format($order->total, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
