@props(['status'])

@php
$map = [
    'en_attente' => ['label' => 'En attente', 'color' => 'amber'],
    'acceptee' => ['label' => 'Acceptée', 'color' => 'blue'],
    'expediee' => ['label' => 'Expédiée', 'color' => 'violet'],
    'livree' => ['label' => 'Livrée', 'color' => 'emerald'],
];
$entry = $map[$status] ?? ['label' => $status, 'color' => 'gray'];
@endphp

<x-badge :color="$entry['color']" {{ $attributes }}>{{ __($entry['label']) }}</x-badge>
