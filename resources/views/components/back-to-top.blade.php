{{--
    Plain x-show, no x-cloak/x-transition here — the same combination
    caused a real, reproducible bug on the product-image zoom lightbox
    (x-cloak fights x-show over the element's cached "original display"
    on init). A very brief flash on first paint if the visitor lands
    already scrolled down (e.g. browser back/forward restoring scroll
    position) is an acceptable trade for a toggle that's actually reliable.
--}}
<button type="button"
        x-data="{ show: false }"
        x-init="
            show = window.scrollY > 400;
            window.addEventListener('scroll', () => { show = window.scrollY > 400; }, { passive: true });
        "
        x-show="show"
        @click="window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
        class="fixed bottom-6 right-6 z-30 inline-flex items-center justify-center h-11 w-11 rounded-full bg-brand-600 hover:bg-brand-700 text-white shadow-lg shadow-black/20 transition-colors"
        aria-label="{{ __('Revenir en haut de page') }}">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
    </svg>
</button>
