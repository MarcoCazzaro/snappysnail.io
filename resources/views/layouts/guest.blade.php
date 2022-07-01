<!DOCTYPE html>
<html lang="{{ $SNAIL_SEO_LANGUAGE || str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.preloads')
        @include('partials.seo', ['page_name' => $page_name ?? 'guest'])

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">
        
        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        @livewireStyles

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}" defer></script>
        <script src="https://kit.fontawesome.com/fea9be3e02.js" crossorigin="anonymous" defer></script>

    </head>
    <body class="antialiased snail-page-{{ $page_name ?? 'guest'}}">
        <div id="snailMainWrapper" class="relative flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 py-4 sm:pt-0">
            {{ $slot }}
        </div>
        <div class="md:fixed bottom-0 min-w-full bg-gray-100 dark:bg-gray-900 p-4">
            <livewire:footer-copyright-line />
        </div>

        @livewireScripts

        <script>
            var ssnailPageAnimation = function(){
                var element = document.getElementById("snailMainWrapper");
                if (element && !element.classList.contains("items-top")) {
                    element.classList.remove("items-center");
                    element.classList.add("items-top");
                }
                for (var i = 1; i <= 3; i++) {
                    element = document.getElementsByClassName("snail-" + i);
                    element[0].setAttribute("style", "animation-play-state: paused; display: none");
                }
                element = document.getElementsByClassName("snail-search");
                element[0].setAttribute("style", "animation-duration: 1s; animation-timing-function: ease;");
                element = document.getElementsByClassName("snail-web-development");
                element[0].setAttribute("style", "animation-delay: 0.13s;");
            };
            @if($whatever ?? false)
                ssnailPageAnimation();
            @endif
            document.addEventListener("DOMContentLoaded", () => {
                Livewire.hook('element.updated', (el, component) => {
                    ssnailPageAnimation();
                })
            });
        </script>

        @stack('scripts')
    </body>
</html>
