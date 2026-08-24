<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 dark:text-gray-100 leading-tight">{{ __('Mentions légales & CGU') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 sm:p-8">
                @if (trim($content) !== '')
                    <div class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $content }}</div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ __("Les mentions légales et conditions générales d'utilisation n'ont pas encore été renseignées par l'administrateur.") }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
