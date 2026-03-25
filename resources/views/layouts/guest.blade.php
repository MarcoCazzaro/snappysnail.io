<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.seo')
    @include('partials.preloads')
    @stack('page-preloads')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://kit.fontawesome.com/fea9be3e02.js" crossorigin="anonymous" defer></script>

    <!-- Styles -->
    @livewireStyles
</head>

<body class="relative antialiased ssnail-page-{{ $page_name ?? 'guest'}}">
    <div id="ssnailMainWrapper" class="relative px-8 flex items-center justify-center min-h-screen bg-gradient-radial from-transparent to-zinc-100 dark:bg-gradient-radial dark:from-zinc-900 dark:to-zinc-950 py-4 sm:pt-0">
        {{ $slot }}
    </div>
    <div class="absolute top-0 right-0 px-6 py-4 flex items-center gap-3">
        <?php
        $currentLocale = app()->getLocale();
        $currentPath = request()->path();
        $pathSuffix = ltrim(substr($currentPath, strlen($currentLocale)), '/');
        $itUrl = url('it' . ($pathSuffix ? '/' . $pathSuffix : ''));
        $enUrl = url('en' . ($pathSuffix ? '/' . $pathSuffix : ''));
        ?>
        <div x-data="{ open: false }" class="relative">
            <button
                type="button"
                aria-label="Switch language"
                @click="open = !open"
                @click.outside="open = false"
                class="border rounded-full px-2 py-[2px] text-brand dark:text-brand border-zinc-400 dark:border-zinc-900 text-sm"
            >{{ $currentLocale === 'it' ? '🇮🇹' : '🇬🇧' }} {{ strtoupper($currentLocale) }} <i class="fas fa-chevron-down text-xs opacity-60"></i></button>
            <div
                x-show="open"
                x-transition
                class="absolute right-0 top-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden z-50 min-w-[80px]"
                style="display: none;"
            >
                <a href="{{ $itUrl }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 {{ $currentLocale === 'it' ? 'font-semibold' : '' }}">🇮🇹 IT</a>
                <a href="{{ $enUrl }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 {{ $currentLocale === 'en' ? 'font-semibold' : '' }}">🇬🇧 EN</a>
            </div>
        </div>
        <button
            aria-label="Toggle dark mode"
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
            class="border rounded-full px-2 text-brand dark:text-brand border-zinc-400 dark:border-zinc-900"
            @click="toggleTheme"><i class="fas fa-moon mr-2" :class="(currentTheme == 'light') ? 'opacity-25' : 'opacity-100'"></i><i class="fas fa-sun" :class="(currentTheme == 'dark') ? 'opacity-25' : 'opacity-100'"></i></button>
    </div>
    <div class="min-w-full bg-zinc-100 dark:bg-zinc-950 p-4">
        <x-footer-copyright-line />
    </div>

    @livewireScripts

    <script>
        var ssnailPageAnimation = function() {
            var element = document.getElementById("ssnailMainWrapper");
            if (element && !element.classList.contains("items-top")) {
                element.classList.remove("items-center");
                element.classList.add("items-top");
            }
            for (var i = 1; i <= 3; i++) {
                element = document.getElementsByClassName("ssnail-" + i);
                element[0].setAttribute("style", "animation-play-state: paused; display: none");
            }
            element = document.getElementsByClassName("ssnail-search");
            element[0].setAttribute("style", "animation-duration: 1s; animation-timing-function: ease;");
            element = document.getElementsByClassName("ssnail-web-development");
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