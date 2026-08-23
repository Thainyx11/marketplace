<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $role = '';

    #[Url(as: 'q')]
    public string $search = '';

    public function toggleActive(User $user): void
    {
        abort_if($user->id === auth()->id(), 403);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function with(): array
    {
        $query = User::query();

        if ($this->role) {
            $query->where('role', $this->role);
        }

        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"));
        }

        return ['users' => $query->latest()->paginate(15)];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <div class="flex gap-3 mb-4">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Nom ou email...') }}"
               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
        <select wire:model.live="role" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
            <option value="">{{ __('Tous les rôles') }}</option>
            <option value="acheteur">{{ __('Acheteur') }}</option>
            <option value="vendeur">{{ __('Vendeur') }}</option>
            <option value="admin">{{ __('Administrateur') }}</option>
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @foreach ($users as $user)
            <div class="flex items-center gap-4 p-4" wire:key="user-{{ $user->id }}">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }} · {{ ['acheteur' => 'Acheteur', 'vendeur' => 'Vendeur', 'admin' => 'Administrateur'][$user->role] }}</p>
                </div>

                <span @class(['text-xs px-2 py-1 rounded-full', 'bg-green-100 text-green-800' => $user->is_active, 'bg-gray-100 text-gray-500' => ! $user->is_active])>
                    {{ $user->is_active ? __('Actif') : __('Désactivé') }}
                </span>

                @if ($user->id !== auth()->id())
                    <button type="button" wire:click="toggleActive({{ $user->id }})" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ $user->is_active ? __('Désactiver') : __('Réactiver') }}
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</div>
