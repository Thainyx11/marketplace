<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink(
                $this->only('email')
            );
        } catch (\Throwable $e) {
            // FIX: a mail-transport failure (e.g. the provider rejecting the
            // recipient) must never surface as a raw 500 to the user, and must
            // not reveal — via a different error than usual — whether the
            // address is registered. Log it and fall through to the same
            // generic "sent" status shown on success.
            Log::error('Password reset email could not be sent: '.$e->getMessage());
            $status = Password::RESET_LINK_SENT;
        }

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Mot de passe oublié') }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Indiquez votre adresse e-mail, nous vous enverrons un lien pour choisir un nouveau mot de passe.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Envoyer le lien de réinitialisation') }}
            </x-primary-button>
        </div>
    </form>
</div>
