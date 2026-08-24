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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-violet-950 via-gray-950 to-gray-900">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo :light="true" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white dark:bg-gray-800 shadow-xl shadow-black/20 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <a href="{{ route('legal-notice') }}" wire:navigate class="mt-6 text-xs text-gray-400 hover:text-gray-200 transition">
                {{ __('Mentions légales & CGU') }}
            </a>
        </div>
    </body>
</html>
