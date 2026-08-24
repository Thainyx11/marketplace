<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profil Marketplace') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Avatar, biographie et adresse de livraison.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">
        <div class="flex items-center gap-4">
            @if (auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-16 h-16 rounded-full object-cover">
            @endif
            <div class="flex-1">
                <x-input-label for="avatar" :value="__('Avatar')" />
                <input type="file" wire:model="avatar" id="avatar" accept="image/*" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="bio" :value="__('Biographie')" />
            <textarea wire:model="bio" id="bio" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="shipping_address" :value="__('Adresse de livraison par défaut')" />
            <textarea wire:model="shipping_address" id="shipping_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
            <x-input-error class="mt-2" :messages="$errors->get('shipping_address')" />
        </div>

        @if (auth()->user()->isVendeur())
            <div>
                <x-input-label for="shop_name" :value="__('Nom de la boutique')" />
                <x-text-input wire:model="shop_name" id="shop_name" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('shop_name')" />
            </div>

            <div>
                <x-input-label for="payout_note" :value="__('Coordonnées de versement (note libre)')" />
                <textarea wire:model="payout_note" id="payout_note" rows="2" placeholder="{{ __('Ex. : IBAN communiqué séparément par email, ce champ est juste un aide-mémoire.') }}"
                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __("Par sécurité, n'inscrivez pas d'IBAN complet ou d'informations bancaires sensibles ici.") }}
                </p>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

            @if (session('marketplace-profile-status'))
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('marketplace-profile-status') }}</p>
            @endif
        </div>
    </form>
</section>
