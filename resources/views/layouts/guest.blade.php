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
    <body class="relative antialiased snail-page-{{ $page_name ?? 'guest'}}">
        <div id="snailMainWrapper" class="relative flex items-center justify-center min-h-screen bg-gradient-radial from-zinc-100 to-zinc-200 dark:bg-gradient-radial dark:from-zinc-900 dark:to-zinc-950 py-4 sm:pt-0">
            {{ $slot }}
        </div>
        <div class="absolute top-0 right-0 px-6 py-4">
            <button
                type="button"
                x-data="{
                    currentTheme: 'dark',
                    applyTheme() {
                        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                            document.documentElement.classList.add('dark');
                            this.currentTheme = 'dark';
                        } else {
                            document.documentElement.classList.remove('dark');
                            this.currentTheme = 'light';
                        }
                    },
                    toggleTheme() {
                        this.currentTheme = (this.currentTheme == 'light' ? 'dark' : 'light');
                        document.documentElement.classList.toggle('dark');
                        localStorage.theme = this.currentTheme;
                    }
                }"
                x-init="applyTheme()"
                class="border rounded-full px-2 border-zinc-400 dark:border-zinc-900"
                @click="toggleTheme"
            ><i class="fas fa-moon mr-2" :class="(currentTheme == 'light') ? 'opacity-25' : 'opacity-100'"></i><i class="fas fa-sun" :class="(currentTheme == 'dark') ? 'opacity-50' : 'opacity-100'"></i></button>
        </div>
        <div class="md:fixed bottom-0 min-w-full bg-zinc-200 dark:bg-zinc-950 p-4">
            <livewire:footer-copyright-line />
            @if (Route::has('login'))
                <div class="hidden absolute top-0 right-5 px-6 py-4 sm:block">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-zinc-100 dark:text-zinc-900 underline">Log in</a>

                        @if (false && Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
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
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
