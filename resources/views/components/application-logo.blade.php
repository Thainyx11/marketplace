@props(['light' => false])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 shrink-0']) }}>
    <span class="grid place-items-center h-8 w-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white shadow-sm shrink-0">
        <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2.5L14.4 9.1L21 11.5L14.4 13.9L12 20.5L9.6 13.9L3 11.5L9.6 9.1L12 2.5Z" fill="currentColor" />
        </svg>
    </span>
    <span @class([
        'text-lg font-extrabold tracking-tight leading-none',
        'text-white' => $light,
        'text-gray-900 dark:text-white' => ! $light,
    ])>
        Pop<span class="text-violet-500">Culture</span>
    </span>
</div>
