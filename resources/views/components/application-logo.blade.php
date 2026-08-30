@props(['light' => false])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 shrink-0']) }}>
    <img src="{{ asset('images/popculture-icon.png') }}"
         srcset="{{ asset('images/popculture-icon.png') }} 1x, {{ asset('images/popculture-icon@2x.png') }} 2x"
         alt="PopCulture" class="h-8 w-auto shrink-0" width="442" height="340">
    <span @class([
        'text-lg font-extrabold tracking-tight leading-none',
        'text-white' => $light,
        'text-gray-900 dark:text-white' => ! $light,
    ])>
        PopCulture
    </span>
</div>
