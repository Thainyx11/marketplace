<?php

use App\Models\PromoCode;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $code = '';

    public string $type = 'percent';

    public string $value = '';

    public ?string $expires_at = null;

    public ?int $max_uses = null;

    public function create(): void
    {
        $data = $this->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['code'] = strtoupper($data['code']);

        PromoCode::create($data);

        $this->reset(['code', 'value', 'expires_at', 'max_uses']);
        $this->type = 'percent';
    }

    public function toggleActive(PromoCode $promoCode): void
    {
        $promoCode->update(['active' => ! $promoCode->active]);
    }

    public function delete(PromoCode $promoCode): void
    {
        $promoCode->delete();
    }

    public function with(): array
    {
        return ['codes' => PromoCode::latest()->get()];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <form wire:submit="create" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 grid grid-cols-2 sm:grid-cols-5 gap-3 items-end mb-6">
        <div>
            <x-input-label for="code" :value="__('Code')" />
            <x-text-input wire:model="code" id="code" class="mt-1.5 w-full text-sm" />
            @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <x-input-label for="type" :value="__('Type')" />
            <select wire:model="type" id="type" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                <option value="percent">%</option>
                <option value="fixed">€</option>
            </select>
        </div>
        <div>
            <x-input-label for="value" :value="__('Valeur')" />
            <x-text-input wire:model="value" id="value" type="number" step="0.01" class="mt-1.5 w-full text-sm" />
            @error('value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <x-input-label for="max_uses" :value="__('Utilisations max')" />
            <x-text-input wire:model="max_uses" id="max_uses" type="number" class="mt-1.5 w-full text-sm" />
        </div>
        <x-primary-button>{{ __('Créer') }}</x-primary-button>
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($codes as $promo)
            <div class="flex items-center gap-4 p-4" wire:key="promo-{{ $promo->id }}">
                <div class="flex-1">
                    <p class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $promo->code }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $promo->type === 'percent' ? $promo->value.'%' : number_format($promo->value, 2, ',', ' ').' €' }}
                        · {{ __('Utilisé') }} {{ $promo->used_count }}{{ $promo->max_uses ? '/'.$promo->max_uses : '' }}
                        @if ($promo->expires_at) · {{ __('Expire le') }} {{ $promo->expires_at->format('d/m/Y') }} @endif
                    </p>
                </div>
                <x-badge :color="$promo->active ? 'emerald' : 'gray'">
                    {{ $promo->active ? __('Actif') : __('Inactif') }}
                </x-badge>
                <button type="button" wire:click="toggleActive({{ $promo->id }})" class="text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                    {{ $promo->active ? __('Désactiver') : __('Activer') }}
                </button>
                <button type="button" wire:click="delete({{ $promo->id }})" wire:confirm="{{ __('Supprimer ce code promo ?') }}" class="text-sm text-red-500 hover:underline">
                    {{ __('Supprimer') }}
                </button>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __('Aucun code promo.') }}</p>
        @endforelse
    </div>
</div>
