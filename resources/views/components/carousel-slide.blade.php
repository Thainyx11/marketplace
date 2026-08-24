@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'w-full shrink-0 '.$class]) }}>
    {{ $slot }}
</div>
