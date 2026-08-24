<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 dark:text-gray-100 leading-tight">{{ __('Mes commandes') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($orders as $order)
                <a href="{{ route('orders.show', $order) }}"
                   class="block bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Commande #:id', ['id' => $order->id]) }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }} — {{ $order->items->count() }} {{ __('article(s)') }}</p>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="font-bold text-gray-900 dark:text-gray-100">{{ number_format($order->total, 2, ',', ' ') }} €</p>
                            <x-order-status-badge :status="$order->status" />
                        </div>
                    </div>
                </a>
            @empty
                <div class="py-16 text-center">
                    <div class="text-5xl mb-3">📦</div>
                    <p class="text-gray-500 dark:text-gray-400">{{ __("Vous n'avez pas encore passé de commande.") }}</p>
                </div>
            @endforelse

            <div>{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
