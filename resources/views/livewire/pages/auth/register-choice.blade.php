<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    //
}; ?>

<div>
    <div class="text-center mb-6">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Créer un compte') }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Choisissez le type de compte qui vous correspond.') }}</p>
    </div>

    <div class="grid gap-4">
        <a href="{{ route('register.acheteur') }}" wire:navigate
           class="block rounded-lg border border-gray-300 dark:border-gray-700 p-4 hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Je suis acheteur') }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Parcourez le catalogue et achetez en toute confiance. Accès immédiat.') }}</p>
        </a>

        <a href="{{ route('register.vendeur') }}" wire:navigate
           class="block rounded-lg border border-gray-300 dark:border-gray-700 p-4 hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Je suis vendeur') }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ouvrez votre boutique et vendez vos objets pop culture. Compte soumis à validation par un administrateur.') }}</p>
        </a>
    </div>

    <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        {{ __('Déjà inscrit ?') }}
        <a href="{{ route('login') }}" wire:navigate class="underline text-indigo-600 dark:text-indigo-400">{{ __('Connectez-vous') }}</a>
    </div>
</div>
