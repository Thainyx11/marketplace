<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
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

    public string $newName = '';

    public string $newEmail = '';

    public string $newPassword = '';

    public string $newRole = 'acheteur';

    public ?int $editingUserId = null;

    public string $editName = '';

    public string $editEmail = '';

    public string $editRole = '';

    public function createUser(): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'newPassword' => ['required', 'string', Password::min(8)],
            'newRole' => ['required', 'in:acheteur,vendeur,admin'],
        ]);

        User::create([
            'name' => $data['newName'],
            'email' => $data['newEmail'],
            'password' => $data['newPassword'],
            'role' => $data['newRole'],
            'shop_name' => $data['newRole'] === 'vendeur' ? $data['newName'] : null,
            'shop_slug' => $data['newRole'] === 'vendeur' ? Str::slug($data['newName']).'-'.Str::lower(Str::random(6)) : null,
        ]);

        $this->reset(['newName', 'newEmail', 'newPassword', 'newRole']);
        $this->newRole = 'acheteur';
        session()->flash('status', __('Utilisateur créé.'));
    }

    public function editUser(User $user): void
    {
        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role;
    }

    public function cancelEdit(): void
    {
        $this->editingUserId = null;
    }

    public function saveUser(): void
    {
        $user = User::findOrFail($this->editingUserId);

        $data = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'editRole' => ['required', 'in:acheteur,vendeur,admin'],
        ]);

        $user->update([
            'name' => $data['editName'],
            'email' => $data['editEmail'],
            'role' => $data['editRole'],
        ]);

        $this->editingUserId = null;
        session()->flash('status', __('Utilisateur modifié.'));
    }

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
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <form wire:submit="createUser" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 grid grid-cols-2 sm:grid-cols-5 gap-3 items-end mb-6">
        <div>
            <x-input-label for="newName" :value="__('Nom')" />
            <x-text-input wire:model="newName" id="newName" class="mt-1.5 w-full text-sm" />
            @error('newName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <x-input-label for="newEmail" :value="__('Email')" />
            <x-text-input wire:model="newEmail" id="newEmail" type="email" class="mt-1.5 w-full text-sm" />
            @error('newEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <x-input-label for="newPassword" :value="__('Mot de passe')" />
            <x-text-input wire:model="newPassword" id="newPassword" type="password" class="mt-1.5 w-full text-sm" />
            @error('newPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <x-input-label for="newRole" :value="__('Rôle')" />
            <select wire:model="newRole" id="newRole" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                <option value="acheteur">{{ __('Acheteur') }}</option>
                <option value="vendeur">{{ __('Vendeur') }}</option>
                <option value="admin">{{ __('Administrateur') }}</option>
            </select>
        </div>
        <x-primary-button>{{ __('Créer') }}</x-primary-button>
    </form>

    <div class="flex gap-3 mb-4">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Nom ou email...') }}"
               class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
        <select wire:model.live="role" class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
            <option value="">{{ __('Tous les rôles') }}</option>
            <option value="acheteur">{{ __('Acheteur') }}</option>
            <option value="vendeur">{{ __('Vendeur') }}</option>
            <option value="admin">{{ __('Administrateur') }}</option>
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @foreach ($users as $user)
            <div class="p-4" wire:key="user-{{ $user->id }}">
                @if ($editingUserId === $user->id)
                    <form wire:submit="saveUser" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                        <div>
                            <x-input-label :value="__('Nom')" />
                            <x-text-input wire:model="editName" class="mt-1 w-full text-sm" />
                            @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-input-label :value="__('Email')" />
                            <x-text-input wire:model="editEmail" type="email" class="mt-1 w-full text-sm" />
                            @error('editEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-input-label :value="__('Rôle')" />
                            <select wire:model="editRole" class="mt-1 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                                <option value="acheteur">{{ __('Acheteur') }}</option>
                                <option value="vendeur">{{ __('Vendeur') }}</option>
                                <option value="admin">{{ __('Administrateur') }}</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <x-primary-button class="text-xs px-3 py-2">{{ __('Enregistrer') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelEdit" class="text-xs px-3 py-2">{{ __('Annuler') }}</x-secondary-button>
                        </div>
                    </form>
                @else
                    <div class="flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }} · {{ ['acheteur' => 'Acheteur', 'vendeur' => 'Vendeur', 'admin' => 'Administrateur'][$user->role] }}</p>
                        </div>

                        <x-badge :color="$user->is_active ? 'emerald' : 'gray'">
                            {{ $user->is_active ? __('Actif') : __('Désactivé') }}
                        </x-badge>

                        <button type="button" wire:click="editUser({{ $user->id }})" class="text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                            {{ __('Modifier') }}
                        </button>

                        @if ($user->id !== auth()->id())
                            <button type="button" wire:click="toggleActive({{ $user->id }})" class="text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                                {{ $user->is_active ? __('Désactiver') : __('Réactiver') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</div>
