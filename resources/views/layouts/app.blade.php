<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-950">
            <livewire:layout.navigation />

            <x-flash-messages />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <footer class="mt-auto border-t border-gray-100 dark:border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>&copy; {{ now()->year }} {{ config('app.name') }}</span>
                    <a href="{{ route('legal-notice') }}" wire:navigate class="hover:text-violet-600 dark:hover:text-violet-400 transition">
                        {{ __('Mentions légales & CGU') }}
                    </a>
                </div>
            </footer>
        </div>

        <x-cookie-banner />
    </body>
</html>
