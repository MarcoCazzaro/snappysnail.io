<x-guest-layout>
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

        <h1 class="mb-9">Dear GoogleBot,</h1>
        <p>this is a page I made especially for you, I prepared all the links you can browse so you don't spend much time looking around.</p>
        <nav class="my-5">
            <div class="my-5"><a href="{{ url('/') }}">Home</a></div>
            <div class="my-5"><a href="{{ url('/portfolio') }}">Portfolio</a></div>
            <div class="my-5"><a href="{{ url('/curriculum') }}">Curriculum</a></div>
            <div class="my-5"><a href="{{ url('/contact') }}">Contact</a></div>
        </nav>
        <p>You are very welcome!</p>
    </div>
</x-guest-layout>