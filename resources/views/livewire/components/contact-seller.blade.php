<div class="mt-4">
    @if ($sent)
        <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">
            {{ __('Message envoyé !') }}
            <a href="{{ route('messages.show', [$product, $product->seller]) }}" wire:navigate class="underline">{{ __('Voir la conversation') }}</a>
        </p>
    @else
        <form wire:submit="send" class="space-y-2">
            <textarea wire:model="content" rows="3" placeholder="{{ __('Votre message au vendeur...') }}"
                      class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500"></textarea>
            @error('content') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

            <button type="submit" class="text-sm font-semibold bg-gray-900 hover:bg-gray-700 dark:bg-gray-100 dark:hover:bg-white dark:text-gray-900 text-white px-4 py-2 rounded-full transition">
                {{ __('Envoyer') }}
            </button>
        </form>
    @endif
</div>
