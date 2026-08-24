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
               'px-3.5 py-1.5 rounded-full font-medium transition',
               'bg-violet-600 text-white shadow-sm' => request()->routeIs($route === 'admin.users.index' ? 'admin.users.*' : ($route === 'admin.orders.index' ? 'admin.orders.*' : $route)),
               'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-gray-700 hover:text-violet-700 dark:hover:text-white' => ! request()->routeIs($route === 'admin.users.index' ? 'admin.users.*' : ($route === 'admin.orders.index' ? 'admin.orders.*' : $route)),
           ])>
            {{ $label }}
        </a>
    @endforeach
</nav>
