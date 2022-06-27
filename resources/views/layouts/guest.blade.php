<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">
        
        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}" defer></script>
        <script src="https://kit.fontawesome.com/fea9be3e02.js" crossorigin="anonymous" defer></script>

        @livewireStyles
    </head>
    <body class="antialiased snail-page-{{ $page_name ?? 'guest'}}">
        <div id="snailMainWrapper" class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 py-4 sm:pt-0">
            {{ $slot }}
        </div>
        <div class="md:fixed bottom-0 min-w-full bg-gray-100 dark:bg-gray-900 p-4">
            <livewire:footer-copyright-line />
        </div>

        @livewireScripts
    </body>
</html>
