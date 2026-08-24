@props(['color' => 'gray'])

@php
$colors = [
    'gray' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300',
    'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
    'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
    'red' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap '.($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
