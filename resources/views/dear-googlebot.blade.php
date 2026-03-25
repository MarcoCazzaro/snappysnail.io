<x-guest-layout page-name="dear-googlebot" :whatever="true">
    <div class="container pb-5">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 ssnail-header">
            <?php
            if (request()->routeIs('locale.home')) {
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
                <a href="{{ route('locale.home', ['locale' => app()->getLocale()]) }}">
                    @endif
                    <{{$first_title_tag}} class="flex justify-center items-center"><img src="{{asset('img/snappysnail-logo.png')}}" class="ssnail-logo mr-3" width="31" height="31" alt=""> Snappysnail</{{$first_title_tag}}>
                    @if($wrapper_tag === 'a')
                </a>
                @endif
            </div>
            <div>
                <{{$second_title_tag}} class="text-center text-sm ssnail-web-development">{{ __('pages.web_development') }}</{{$second_title_tag}}>
            </div>
        </div>

        <div class="p-8">
            <h1 class="mb-9"><i class="fas fa-robot"></i> Dear GoogleBot,</h1>
            <p>{{ __('pages.googlebot_intro') }}</p>
            <nav class="my-5">
                <div class="my-5"><a href="{{ route('locale.home', ['locale' => app()->getLocale()]) }}">Home</a></div>
                <div class="my-5"><a href="{{ url(app()->getLocale() . '/services') }}">Services</a></div>
                <div class="my-5"><a href="{{ url(app()->getLocale() . '/portfolio') }}">Portfolio</a></div>
                <div class="my-5"><a href="{{ url(app()->getLocale() . '/curriculum') }}">Curriculum</a></div>
                <div class="my-5"><a href="{{ url(app()->getLocale() . '/contact') }}">Contact</a></div>
            </nav>
            <p>{{ __('pages.googlebot_thanks') }}</p>
        </div>
    </div>
</x-guest-layout>
