@props(['slug'])

{{--
    Hand-drawn line icons (stroke-based, matching the "pas d'image" placeholder
    style already used on product cards) — replaces the emoji category icons
    for a look consistent with the rest of the navy identity.
--}}

@switch($slug)
    @case('cartes-a-collectionner')
        <svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2.5" y="7" width="11" height="15" rx="2" transform="rotate(-10 8 14.5)" />
            <rect x="8" y="3" width="13" height="17" rx="2" fill="currentColor" fill-opacity=".08" />
        </svg>
        @break

    @case('jeux-video')
        <svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.5 9h11A3.5 3.5 0 0121 12.5v3a2.5 2.5 0 01-4.6 1.4L15 15H9l-1.4 1.9A2.5 2.5 0 013 15.5v-3A3.5 3.5 0 016.5 9z" />
            <path d="M8 11.2v2.6M6.7 12.5h2.6" />
            <circle cx="16.2" cy="11.6" r=".55" fill="currentColor" stroke="none" />
            <circle cx="18" cy="13.4" r=".55" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('figurines')
        <svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="6.5" r="3" />
            <path d="M6.5 21v-4.5a5.5 5.5 0 0111 0V21" />
        </svg>
        @break

    @case('manga')
        <svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 5.3c1.6-.8 3.6-1 5.1-.6A3.8 3.8 0 0112 5.6a3.8 3.8 0 012.9-.9c1.5-.4 3.5-.2 5.1.6v13c-1.6-.8-3.6-1-5.1-.6a3.8 3.8 0 00-2.9.9 3.8 3.8 0 00-2.9-.9c-1.5-.4-3.5-.2-5.1.6v-13z" />
            <path d="M12 5.6v13" />
        </svg>
        @break

    @case('goodies')
        <svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="4" y="10.5" width="16" height="9.5" rx="1.5" />
            <path d="M4 10.5h16M12 10.5V20" />
            <path d="M12 10.5c-1.8-3.2-6-3-6-.7 0 1.5 2.2 1.7 6 .7zM12 10.5c1.8-3.2 6-3 6-.7 0 1.5-2.2 1.7-6 .7z" />
        </svg>
        @break

    @default
        <svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="4" y="4" width="16" height="16" rx="2" />
        </svg>
@endswitch
