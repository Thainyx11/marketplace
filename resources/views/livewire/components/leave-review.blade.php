<div class="mt-3 bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
    @if ($existing)
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Votre avis') }}</p>
        <p class="text-amber-500">{{ str_repeat('★', $existing->rating) }}{{ str_repeat('☆', 5 - $existing->rating) }}</p>
        @if ($existing->comment)
            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $existing->comment }}</p>
        @endif
    @else
        <form wire:submit="submit" class="space-y-2">
            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Laisser un avis') }}</label>

            <select wire:model="rating" class="block rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
                @foreach ([5, 4, 3, 2, 1] as $n)
                    <option value="{{ $n }}">{{ str_repeat('★', $n) }} ({{ $n }}/5)</option>
                @endforeach
            </select>

            <textarea wire:model="comment" rows="2" placeholder="{{ __('Votre commentaire (optionnel)') }}"
                      class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500"></textarea>
            @error('comment') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

            <button type="submit" class="text-sm font-semibold bg-gray-900 hover:bg-gray-700 dark:bg-gray-100 dark:hover:bg-white dark:text-gray-900 text-white px-4 py-2 rounded-full transition">
                {{ __('Envoyer mon avis') }}
            </button>
        </form>
    @endif
</div>
