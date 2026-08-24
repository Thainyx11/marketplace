<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-violet-600 border border-transparent rounded-full font-semibold text-sm text-white shadow-sm shadow-violet-600/20 hover:bg-violet-500 hover:shadow-md active:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:pointer-events-none transition-all duration-150']) }}>
    {{ $slot }}
</button>
