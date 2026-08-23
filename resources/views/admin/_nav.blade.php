@php
    $links = [
        'admin.dashboard' => __('Vue d\'ensemble'),
        'admin.users.index' => __('Utilisateurs'),
        'admin.vendors' => __('Vendeurs'),
        'admin.categories' => __('Catégories'),
        'admin.products' => __('Produits'),
        'admin.orders.index' => __('Commandes'),
        'admin.reviews' => __('Avis'),
        'admin.message-reports' => __('Messages signalés'),
        'admin.promo-codes' => __('Codes promo'),
        'admin.settings' => __('Paramètres'),
    ];
@endphp

<nav class="flex flex-wrap gap-2 mb-6 text-sm">
    @foreach ($links as $route => $label)
        <a href="{{ route($route) }}" wire:navigate
           @class([
               'px-3 py-1.5 rounded-lg',
               'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' => request()->routeIs($route === 'admin.users.index' ? 'admin.users.*' : ($route === 'admin.orders.index' ? 'admin.orders.*' : $route)),
               'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => ! request()->routeIs($route === 'admin.users.index' ? 'admin.users.*' : ($route === 'admin.orders.index' ? 'admin.orders.*' : $route)),
           ])>
            {{ $label }}
        </a>
    @endforeach
</nav>
