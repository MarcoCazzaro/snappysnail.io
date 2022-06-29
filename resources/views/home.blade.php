<x-guest-layout page-name="home" :whatever="$whatever ?? null" >
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

    <div class="container pb-5">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 ssnail-header">
            <?php
                if (Request::is('/')) {
                    $first_title_tag = "h1";
                    $second_title_tag = "h2";
                    $wrapper_tag = false;
                } else {
                    $first_title_tag = "h2";
                    $second_title_tag = "h3";
                    $wrapper_tag = "a";
                }
            ?>
            <div class="flex justify-center pt-8">
                @if($wrapper_tag === 'a')
                    <a href="{{ url('/') }}">
                @endif
                    <{{$first_title_tag}} class="flex justify-center items-center"><img src="{{asset('img/snappysnail-logo.png')}}" class="snail-logo mr-3" width="31" height="31" alt="Snappysnail"> Snappysnail</{{$first_title_tag}}>
                @if($wrapper_tag === 'a')
                    </a>
                @endif
            </div>
            <div>
                <{{$second_title_tag}} class="text-center text-sm snail-web-development">Web development</{{$second_title_tag}}>
            </div>
        </div>

        @livewire('snail-search', ['searchTerms' => $whatever ?? null])

    </div>
</x-guest-layout>