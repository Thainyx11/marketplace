@props(['light' => false])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 shrink-0']) }}>
    <svg viewBox="0 0 24 24" class="h-7 w-7 shrink-0 {{ $light ? 'text-white' : 'text-brand-900 dark:text-white' }}" xmlns="http://www.w3.org/2000/svg">
        <rect x="3" y="6" width="10" height="14" rx="2" fill="currentColor" fill-opacity="0.35" transform="rotate(-16 8 13)" />
        <rect x="11" y="6" width="10" height="14" rx="2" fill="currentColor" fill-opacity="0.6" transform="rotate(16 16 13)" />
        <rect x="7" y="4" width="10" height="14" rx="2" fill="currentColor" />
    </svg>
    <span @class([
        'text-lg font-extrabold tracking-tight leading-none',
        'text-white' => $light,
        'text-gray-900 dark:text-white' => ! $light,
    ])>
        PopCulture
    </span>
</div>
