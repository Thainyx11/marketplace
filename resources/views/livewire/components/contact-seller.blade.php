<div class="mt-4">
    @if ($sent)
        <p class="text-sm text-green-600 dark:text-green-400">
            {{ __('Message envoyé !') }}
            <a href="{{ route('messages.show', [$product, $product->seller]) }}" wire:navigate class="underline">{{ __('Voir la conversation') }}</a>
        </p>
    @else
        <form wire:submit="send" class="space-y-2">
            <textarea wire:model="content" rows="3" placeholder="{{ __('Votre message au vendeur...') }}"
                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
            @error('content') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

            <button type="submit" class="text-sm bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                {{ __('Envoyer') }}
            </button>
        </form>
    @endif
</div>
