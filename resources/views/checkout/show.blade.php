<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Commande') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-lg p-4 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Récapitulatif') }}</h3>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($items as $entry)
                        <div class="flex items-center justify-between py-2 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $entry['product']->title }} × {{ $entry['quantity'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($entry['product']->price * $entry['quantity'], 2, ',', ' ') }} €</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Total') }}</span>
                    <span class="font-bold text-lg text-indigo-600 dark:text-indigo-400">{{ number_format($total, 2, ',', ' ') }} €</span>
                </div>
            </div>

            <form method="POST" action="{{ route('checkout.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                @csrf

                <div>
                    <x-input-label for="shipping_address" :value="__('Adresse de livraison')" />
                    <textarea name="shipping_address" id="shipping_address" rows="3" required maxlength="500"
                              class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">{{ old('shipping_address', auth()->user()->shipping_address) }}</textarea>
                    <x-input-error :messages="$errors->get('shipping_address')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="promo_code" :value="__('Code promo (optionnel)')" />
                    <x-text-input name="promo_code" id="promo_code" type="text" class="mt-1 w-full" :value="old('promo_code')" />
                    <x-input-error :messages="$errors->get('promo_code')" class="mt-2" />
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Vous serez redirigé vers Stripe pour renseigner vos informations de paiement en toute sécurité.') }}
                </p>

                <x-primary-button>{{ __('Payer avec Stripe') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
