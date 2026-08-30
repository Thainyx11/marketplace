<?php

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $commission_rate = '5';

    public string $legal_notice = '';

    /** Defense-in-depth: the route group already requires role:admin. */
    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

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

        $message = __('Paramètres enregistrés.');
        session()->flash('status', $message);
        // FIX: a pure Livewire action never reaches <x-flash-messages> (it
        // lives outside this component's AJAX render boundary) — see
        // resources/views/components/flash-messages.blade.php.
        $this->dispatch('flash-message', message: $message, type: 'status');
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 space-y-4">
        <div>
            <x-input-label for="commission_rate" :value="__('Taux de commission (%)')" />
            <x-text-input wire:model="commission_rate" id="commission_rate" type="number" step="0.1" min="0" max="100" class="mt-1.5 w-40" />
            @error('commission_rate') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Appliqué sur chaque nouvelle transaction (5% par défaut).') }}</p>
        </div>

        <div>
            <x-input-label for="legal_notice" :value="__('Mentions légales / CGU')" />
            <textarea wire:model="legal_notice" id="legal_notice" rows="10"
                      class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
            @error('legal_notice') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Visible publiquement sur') }}
                <a href="{{ route('legal-notice') }}" target="_blank" class="underline">{{ url('/mentions-legales') }}</a>
            </p>
        </div>

        <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
    </form>
</div>
