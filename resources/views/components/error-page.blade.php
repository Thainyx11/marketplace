@props(['code', 'title', 'message'])

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.theme-init')

        <title>{{ $code }} — {{ $title }} · {{ config('app.name', 'PopCulture') }}</title>

        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/favicon-512.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-6 text-center bg-gradient-to-br from-brand-950 via-gray-950 to-gray-900">
            <a href="/">
                <x-application-logo :light="true" />
            </a>

            <p class="mt-10 text-7xl sm:text-8xl font-extrabold bg-gradient-to-r from-brand-300 to-brand-500 bg-clip-text text-transparent">
                {{ $code }}
            </p>
            <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-white">{{ $title }}</h1>
            <p class="mt-3 max-w-md text-gray-300">{{ $message }}</p>

            <a href="/" class="mt-8 inline-flex items-center justify-center px-6 py-3 bg-white text-brand-900 font-semibold rounded-full hover:bg-gray-100 transition">
                {{ __("Retour à l'accueil") }}
            </a>
        </div>
    </body>
</html>
