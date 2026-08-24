<?php

use App\Livewire\Forms\LoginForm;
use App\Services\CartManager;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        CartManager::mergeSessionIntoDb(auth()->user());

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Connexion') }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Ravi de vous revoir.') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-brand-700 shadow-sm focus:ring-brand-500 dark:focus:ring-brand-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Se souvenir de moi') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Se connecter') }}
            </x-primary-button>
        </div>
    </form>

    <p class="text-sm text-gray-500 dark:text-gray-400 mt-6 text-center">
        {{ __("Pas encore de compte ?") }}
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">{{ __("S'inscrire") }}</a>
    </p>
</div>
