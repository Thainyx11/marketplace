<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Mes commandes') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($orders as $order)
                <a href="{{ route('orders.show', $order) }}"
                   class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Commande #:id', ['id' => $order->id]) }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }} — {{ $order->items->count() }} {{ __('article(s)') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($order->total, 2, ',', ' ') }} €</p>
                            <x-order-status-badge :status="$order->status" />
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 dark:text-gray-400 py-12 text-center">{{ __("Vous n'avez pas encore passé de commande.") }}</p>
            @endforelse

            <div>{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
