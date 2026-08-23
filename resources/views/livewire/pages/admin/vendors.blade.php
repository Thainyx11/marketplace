<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function approve(User $vendor): void
    {
        abort_unless($vendor->isVendeur(), 404);
        $vendor->update(['is_approved' => true]);
    }

    public function reject(User $vendor): void
    {
        abort_unless($vendor->isVendeur(), 404);
        $vendor->delete();
    }

    public function suspend(User $vendor): void
    {
        abort_unless($vendor->isVendeur(), 404);
        $vendor->update(['is_active' => ! $vendor->is_active]);
    }

    public function with(): array
    {
        return [
            'pending' => User::where('role', 'vendeur')->where('is_approved', false)->latest()->get(),
            'approved' => User::where('role', 'vendeur')->where('is_approved', true)->withCount('products')->latest()->get(),
        ];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('En attente de validation') }}</h2>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700 mb-8">
        @forelse ($pending as $vendor)
            <div class="flex items-center gap-4 p-4" wire:key="pending-{{ $vendor->id }}">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $vendor->shop_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $vendor->name }} · {{ $vendor->email }}</p>
                </div>
                <button type="button" wire:click="approve({{ $vendor->id }})" class="text-sm bg-green-600 hover:bg-green-500 text-white px-3 py-1.5 rounded-lg">
                    {{ __('Approuver') }}
                </button>
                <button type="button" wire:click="reject({{ $vendor->id }})" wire:confirm="{{ __('Rejeter et supprimer ce compte vendeur ?') }}"
                        class="text-sm text-red-500 hover:underline">
                    {{ __('Rejeter') }}
                </button>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __('Aucune demande en attente.') }}</p>
        @endforelse
    </div>

    <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Vendeurs approuvés') }}</h2>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($approved as $vendor)
            <div class="flex items-center gap-4 p-4" wire:key="approved-{{ $vendor->id }}">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $vendor->shop_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $vendor->email }} · {{ $vendor->products_count }} {{ __('produits') }}</p>
                </div>
                <span @class(['text-xs px-2 py-1 rounded-full', 'bg-green-100 text-green-800' => $vendor->is_active, 'bg-gray-100 text-gray-500' => ! $vendor->is_active])>
                    {{ $vendor->is_active ? __('Actif') : __('Suspendu') }}
                </span>
                <button type="button" wire:click="suspend({{ $vendor->id }})" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ $vendor->is_active ? __('Suspendre') : __('Réactiver') }}
                </button>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __('Aucun vendeur approuvé.') }}</p>
        @endforelse
    </div>
</div>
