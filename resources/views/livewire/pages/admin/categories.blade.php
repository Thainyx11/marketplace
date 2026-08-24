<?php

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $name = '';

    public ?int $parent_id = null;

    public function create(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255']]);

        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name).'-'.Str::lower(Str::random(4)),
            'parent_id' => $this->parent_id,
        ]);

        $this->reset(['name', 'parent_id']);
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            session()->flash('error', __('Impossible de supprimer : catégorie utilisée par des produits ou sous-catégories.'));

            return;
        }

        $category->delete();
    }

    public function with(): array
    {
        return ['categories' => Category::withCount('products')->orderBy('name')->get()];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <form wire:submit="create" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 flex items-end gap-3 mb-6">
        <div class="flex-1">
            <x-input-label for="name" :value="__('Nouvelle catégorie')" />
            <x-text-input wire:model="name" id="name" class="mt-1 w-full" />
            @error('name') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex-1">
            <x-input-label for="parent_id" :value="__('Catégorie parente (optionnel)')" />
            <select wire:model="parent_id" id="parent_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                <option value="">{{ __('Aucune') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <x-primary-button>{{ __('Ajouter') }}</x-primary-button>
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @foreach ($categories as $cat)
            <div class="flex items-center gap-4 p-4" wire:key="cat-{{ $cat->id }}">
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $cat->parent_id ? '— ' : '' }}{{ $cat->name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $cat->products_count }} {{ __('produits') }}</p>
                </div>
                <button type="button" wire:click="delete({{ $cat->id }})" wire:confirm="{{ __('Supprimer cette catégorie ?') }}"
                        class="text-sm text-red-500 hover:underline">
                    {{ __('Supprimer') }}
                </button>
            </div>
        @endforeach
    </div>
</div>
