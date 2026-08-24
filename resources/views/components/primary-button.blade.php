<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-brand-800 border border-transparent rounded-full font-semibold text-sm text-white shadow-sm shadow-brand-800/20 hover:bg-brand-700 hover:shadow-md active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-700 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:pointer-events-none transition-all duration-150']) }}>
    {{ $slot }}
</button>
