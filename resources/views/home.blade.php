<x-guest-layout :page-name="$whatever ?? 'home'" :whatever="$whatever ?? null">
    <div x-data="{
        animating: {{ ($whatever ?? false) ? 'false' : 'true' }},
    }" class="w-full pb-5" :class="animating ? 'ssnail-animation' : 'min-h-[80vh]'">
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
                    <{{$first_title_tag}} class="flex justify-center items-center"><img src="{{asset('img/snappysnail-logo.png')}}" class="ssnail-logo mr-3" width="31" height="31" alt=""> Snappysnail</{{$first_title_tag}}>
                    @if($wrapper_tag === 'a')
                </a>
                @endif
            </div>
            <div>
                <{{$second_title_tag}} class="text-center text-sm ssnail-web-development">Web development</{{$second_title_tag}}>
            </div>
        </div>

        <livewire:ssnail-search :search-terms="$whatever ?? null" />
    </div>
</x-guest-layout>