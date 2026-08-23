<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Merci !') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-10">
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('Paiement reçu 🎉') }}</p>
                <p class="text-gray-600 dark:text-gray-400 mt-3">
                    {{ __("Votre commande est en cours de traitement. Vous la retrouverez dans quelques instants dans « Mes commandes ».") }}
                </p>
                <a href="{{ route('orders.index') }}" wire:navigate
                   class="inline-block mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-lg">
                    {{ __('Voir mes commandes') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
