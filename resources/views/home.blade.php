<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.preloads')
        @include('partials.seo')

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ url(mix('css/app.css')) }}" rel="stylesheet">

        <style>
            html, body {
                height: 100vh;
            }
            h1, h2 {
                font-weight: 100;
                color: #fcbe03;
            }
        </style>

        @livewireStyles
    </head>
    <body class="antialiased snail-page-home">
        <div id="snailMainWrapper" class="relative flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 py-4 sm:pt-0">
            @if (Route::has('login'))
                <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-100 underline">Log in</a>

                        @if (false && Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="container">
                <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                    <div class="flex justify-center pt-8">
                        <h1 class="flex justify-center items-center"><img src="{{asset('img/snappysnail-logo.png')}}" class="snail-logo mr-3" width="31" height="31"> Snappysnail</h1>
                    </div>
                    <div>
                        <h2 class="text-center text-sm snail-web-development">Web development</h2>
                    </div>
                </div>

                @livewire('snail-search')
                
            </div>
        </div>
        <div class="md:fixed bottom-0 min-w-full bg-gray-100 dark:bg-gray-900 p-4">
            <p class="text-center text-gray-400">&copy;{{ date('Y') }} Snappysnail di Marco Cazzaro | Via IV Novembre 9 22045 Lambrugo (CO) Italy | P.IVA 03919560130</p>
        </div>

        @livewireScripts

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Livewire.hook('element.updated', (el, component) => {
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
                })
            });
        </script>
    </body>
</html>
