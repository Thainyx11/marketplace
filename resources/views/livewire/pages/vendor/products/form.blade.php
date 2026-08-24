<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public string $title = '';

    public string $description = '';

    public ?int $category_id = null;

    public string $price = '';

    public string $stock = '0';

    public string $condition = 'bon_etat';

    public string $brand = '';

    public string $rarity = '';

    public string $status = 'active';

    /** @var array<int, mixed> */
    public array $newImages = [];

    public $existingImages;

    public function mount(?Product $product = null): void
    {
        $this->existingImages = collect();

        if ($product?->exists) {
            Gate::authorize('update', $product);

            $this->product = $product;
            $this->title = $product->title;
            $this->description = $product->description;
            $this->category_id = $product->category_id;
            $this->price = (string) $product->price;
            $this->stock = (string) $product->stock;
            $this->condition = $product->condition;
            $this->brand = (string) $product->brand;
            $this->rarity = (string) $product->rarity;
            $this->status = $product->status;
            $this->existingImages = $product->images()->orderBy('position')->get();
        } else {
            Gate::authorize('create', Product::class);
        }
    }

    public function removeImage(int $imageId): void
    {
        $image = ProductImage::findOrFail($imageId);
        Gate::authorize('update', $image->product);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        $this->existingImages = $this->existingImages->reject(fn ($i) => $i->id === $imageId)->values();
    }

    public function save(): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'in:neuf,comme_neuf,bon_etat,usage'],
            'brand' => ['nullable', 'string', 'max:255'],
            'rarity' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,hidden'],
            'newImages.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $imageFiles = $data['newImages'] ?? [];
        unset($data['newImages']);

        if ($this->product) {
            Gate::authorize('update', $this->product);
            $this->product->update($data);
        } else {
            Gate::authorize('create', Product::class);

            $data['user_id'] = auth()->id();
            $data['slug'] = $this->uniqueSlug($data['title']);

            $this->product = Product::create($data);
        }

        $nextPosition = $this->product->images()->max('position') + 1;

        foreach ($imageFiles as $i => $file) {
            $path = $file->store('products', 'public');

            ProductImage::create([
                'product_id' => $this->product->id,
                'path' => $path,
                'position' => $nextPosition + $i,
            ]);
        }

        session()->flash('status', __('Produit enregistré.'));

        $this->redirect(route('vendor.products.index'), navigate: true);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function with(): array
    {
        return ['categories' => Category::orderBy('name')->get()];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">
        {{ $product ? __('Modifier le produit') : __('Nouveau produit') }}
    </h1>

    <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 space-y-4">
        <div>
            <x-input-label for="title" :value="__('Titre')" />
            <x-text-input wire:model="title" id="title" class="mt-1.5 w-full" />
            @error('title') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea wire:model="description" id="description" rows="4" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500"></textarea>
            @error('description') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="category_id" :value="__('Catégorie')" />
                <select wire:model="category_id" id="category_id" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                    <option value="">{{ __('Choisir...') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-input-label for="condition" :value="__('État')" />
                <select wire:model="condition" id="condition" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                    <option value="neuf">{{ __('Neuf') }}</option>
                    <option value="comme_neuf">{{ __('Comme neuf') }}</option>
                    <option value="bon_etat">{{ __('Bon état') }}</option>
                    <option value="usage">{{ __('Usagé') }}</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="price" :value="__('Prix (€)')" />
                <x-text-input wire:model="price" id="price" type="number" step="0.01" min="0" class="mt-1.5 w-full" />
                @error('price') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <x-input-label for="stock" :value="__('Stock')" />
                <x-text-input wire:model="stock" id="stock" type="number" min="0" class="mt-1.5 w-full" />
                @error('stock') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="brand" :value="__('Marque / série (optionnel)')" />
                <x-text-input wire:model="brand" id="brand" class="mt-1.5 w-full" />
            </div>
            <div>
                <x-input-label for="rarity" :value="__('Rareté (optionnel, cartes)')" />
                <x-text-input wire:model="rarity" id="rarity" class="mt-1.5 w-full" />
            </div>
        </div>

        <div>
            <x-input-label for="status" :value="__('Visibilité')" />
            <select wire:model="status" id="status" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                <option value="active">{{ __('En vente') }}</option>
                <option value="hidden">{{ __('Masqué') }}</option>
            </select>
        </div>

        <div>
            <x-input-label :value="__('Photos')" />

            @if ($existingImages->isNotEmpty())
                <div class="grid grid-cols-4 gap-2 mt-2">
                    @foreach ($existingImages as $image)
                        <div class="relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden group" wire:key="img-{{ $image->id }}">
                            <img src="{{ Storage::url($image->path) }}" class="object-cover w-full h-full">
                            <button type="button" wire:click="removeImage({{ $image->id }})"
                                    class="absolute top-1 right-1 bg-red-600 hover:bg-red-500 text-white text-xs rounded-full w-5 h-5 transition">×</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <input type="file" wire:model="newImages" multiple accept="image/*" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            @error('newImages.*') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('vendor.products.index') }}" wire:navigate class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 self-center transition">{{ __('Annuler') }}</a>
            <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
        </div>
    </form>
</div>
