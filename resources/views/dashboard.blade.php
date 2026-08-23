<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg p-4 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @php $user = auth()->user(); @endphp

            @if ($user->isVendeur() && ! $user->is_approved)
                <div class="bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-lg p-4 text-sm">
                    {{ __("Votre compte vendeur est en attente de validation par un administrateur. Vous pourrez publier des produits dès qu'il sera approuvé.") }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-lg font-medium">{{ __('Bonjour :name', ['name' => $user->name]) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Rôle') }} :
                        <span class="font-medium">{{ ['acheteur' => 'Acheteur', 'vendeur' => 'Vendeur', 'admin' => 'Administrateur'][$user->role] ?? $user->role }}</span>
                    </p>
                </div>
            </div>

            @if ($user->isAcheteur())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('orders.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Mes commandes') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Suivre mes achats et mes factures') }}</p>
                    </a>
                    <a href="{{ route('cart.show') }}" wire:navigate class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('Mon panier') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Finaliser mes achats en cours') }}</p>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
