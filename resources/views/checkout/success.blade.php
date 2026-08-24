<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 dark:text-gray-100 leading-tight">{{ __('Merci !') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-10">
                <div class="mx-auto h-16 w-16 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mt-5">{{ __('Paiement reçu 🎉') }}</p>
                <p class="text-gray-600 dark:text-gray-400 mt-3">
                    {{ __("Votre commande est en cours de traitement. Vous la retrouverez dans quelques instants dans « Mes commandes ».") }}
                </p>
                <a href="{{ route('orders.index') }}" wire:navigate
                   class="inline-block mt-6 bg-brand-800 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-full transition">
                    {{ __('Voir mes commandes') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
