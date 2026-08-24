<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    //
}; ?>

<div>
    <div class="text-center mb-6">
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Créer un compte') }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Choisissez le type de compte qui vous correspond.') }}</p>
    </div>

    <div class="grid gap-3">
        <a href="{{ route('register.acheteur') }}" wire:navigate
           class="flex items-start gap-3 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 hover:border-brand-400 hover:bg-brand-50 dark:hover:bg-gray-700 transition">
            <span class="text-2xl">🛍️</span>
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Je suis acheteur') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Parcourez le catalogue et achetez en toute confiance. Accès immédiat.') }}</p>
            </div>
        </a>

        <a href="{{ route('register.vendeur') }}" wire:navigate
           class="flex items-start gap-3 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 hover:border-brand-400 hover:bg-brand-50 dark:hover:bg-gray-700 transition">
            <span class="text-2xl">🏪</span>
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Je suis vendeur') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ouvrez votre boutique et vendez vos objets pop culture. Compte soumis à validation par un administrateur.') }}</p>
            </div>
        </a>
    </div>

    <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        {{ __('Déjà inscrit ?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">{{ __('Connectez-vous') }}</a>
    </div>
</div>
