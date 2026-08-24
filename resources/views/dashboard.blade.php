<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php $user = auth()->user(); @endphp

            @if ($user->isVendeur() && ! $user->is_approved)
                <div class="bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-2xl p-4 text-sm">
                    {{ __("Votre compte vendeur est en attente de validation par un administrateur. Vous pourrez publier des produits dès qu'il sera approuvé.") }}
                </div>
            @endif

            <div class="bg-gradient-to-br from-brand-800 to-brand-900 rounded-2xl shadow-sm overflow-hidden text-white">
                <div class="p-6 flex items-center gap-4">
                    <span class="grid place-items-center h-14 w-14 rounded-2xl bg-white/15 font-extrabold text-2xl shrink-0">
                        {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    </span>
                    <div>
                        <p class="text-lg font-bold">{{ __('Bonjour :name', ['name' => $user->name]) }}</p>
                        <p class="text-sm text-brand-100 mt-0.5">
                            {{ ['acheteur' => 'Compte acheteur', 'vendeur' => 'Compte vendeur', 'admin' => 'Compte administrateur'][$user->role] ?? $user->role }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @if ($user->isAcheteur())
                    <a href="{{ route('orders.index') }}" class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <span class="text-2xl">📦</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Mes commandes') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Suivre mes achats et mes factures') }}</p>
                        </div>
                    </a>
                    <a href="{{ route('cart.show') }}" wire:navigate class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <span class="text-2xl">🛒</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Mon panier') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Finaliser mes achats en cours') }}</p>
                        </div>
                    </a>
                @endif

                @if ($user->isVendeur() || $user->isAdmin())
                    <a href="{{ route('vendor.products.index') }}" wire:navigate class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <span class="text-2xl">🏷️</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Mes produits') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Gérer votre catalogue') }}</p>
                        </div>
                    </a>
                    <a href="{{ route('vendor.orders') }}" wire:navigate class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <span class="text-2xl">📬</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Commandes reçues') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Suivre et expédier vos ventes') }}</p>
                        </div>
                    </a>
                @endif

                @if ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <span class="text-2xl">🛠️</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Administration') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Statistiques, modération et paramètres') }}</p>
                        </div>
                    </a>
                @endif

                <a href="{{ route('messages.index') }}" wire:navigate class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <span class="text-2xl">💬</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Messages') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Vos conversations') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
