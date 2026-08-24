<div x-data="{ show: false }"
     x-init="show = ! localStorage.getItem('cookie-banner-dismissed')"
     x-show="show"
     x-transition
     x-cloak
     class="fixed inset-x-0 bottom-0 z-50 p-4">
    <div class="max-w-3xl mx-auto bg-gray-900 dark:bg-gray-800 text-white rounded-2xl shadow-2xl shadow-black/30 p-4 sm:p-5 flex flex-col sm:flex-row items-center gap-4">
        <p class="text-sm text-gray-300 flex-1">
            {{ __("Ce site utilise uniquement des cookies strictement nécessaires à son fonctionnement (connexion, panier, sécurité). Aucun cookie publicitaire ou de suivi n'est utilisé.") }}
            <a href="{{ route('legal-notice') }}" wire:navigate class="underline hover:text-white">{{ __('En savoir plus') }}</a>
        </p>
        <button type="button"
                @click="localStorage.setItem('cookie-banner-dismissed', '1'); show = false"
                class="shrink-0 bg-brand-800 hover:bg-brand-700 text-white font-semibold text-sm px-5 py-2.5 rounded-full transition">
            {{ __("J'ai compris") }}
        </button>
    </div>
</div>
