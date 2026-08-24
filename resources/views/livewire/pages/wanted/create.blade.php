<?php

use App\Models\Category;
use App\Models\WantedItem;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $title = '';

    public string $description = '';

    public ?int $category_id = null;

    public ?float $max_price = null;

    public function mount(): void
    {
        Gate::authorize('create', WantedItem::class);
    }

    public function save(): void
    {
        Gate::authorize('create', WantedItem::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = WantedItem::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => 'ouverte',
        ]);

        $this->redirect(route('wanted.show', $item), navigate: true);
    }

    public function with(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
        ];
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Publier une recherche') }}</h1>

    <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 space-y-5">
        <div>
            <x-input-label for="title" :value="__('Que recherchez-vous ?')" />
            <x-text-input wire:model="title" id="title" type="text" class="mt-1.5 w-full" placeholder="{{ __('Ex : Charizard VMAX en état neuf') }}" />
            @error('title') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <x-input-label for="description" :value="__('Détails (optionnel)')" />
            <textarea wire:model="description" id="description" rows="4"
                      class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500"
                      placeholder="{{ __('État souhaité, édition, quantité...') }}"></textarea>
            @error('description') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="category_id" :value="__('Catégorie (optionnel)')" />
                <select wire:model="category_id" id="category_id" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">{{ __('Toutes les catégories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-input-label for="max_price" :value="__('Budget maximum en € (optionnel)')" />
                <x-text-input wire:model="max_price" id="max_price" type="number" step="0.01" min="0" class="mt-1.5 w-full" />
                @error('max_price') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <x-primary-button type="submit">{{ __('Publier') }}</x-primary-button>
    </form>
</div>
