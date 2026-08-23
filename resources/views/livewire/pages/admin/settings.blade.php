<?php

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $commission_rate = '5';

    public string $legal_notice = '';

    public function mount(): void
    {
        $this->commission_rate = (string) Setting::get('commission_rate', 5);
        $this->legal_notice = (string) Setting::get('legal_notice', '');
    }

    public function save(): void
    {
        $this->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'legal_notice' => ['nullable', 'string', 'max:20000'],
        ]);

        Setting::set('commission_rate', $this->commission_rate);
        Setting::set('legal_notice', $this->legal_notice);

        session()->flash('status', __('Paramètres enregistrés.'));
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    @if (session('status'))
        <div class="bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg p-4 text-sm mb-6">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
        <div>
            <x-input-label for="commission_rate" :value="__('Taux de commission (%)')" />
            <x-text-input wire:model="commission_rate" id="commission_rate" type="number" step="0.1" min="0" max="100" class="mt-1 w-40" />
            @error('commission_rate') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Appliqué sur chaque nouvelle transaction (5% par défaut).') }}</p>
        </div>

        <div>
            <x-input-label for="legal_notice" :value="__('Mentions légales / CGU')" />
            <textarea wire:model="legal_notice" id="legal_notice" rows="10"
                      class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
            @error('legal_notice') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
    </form>
</div>
