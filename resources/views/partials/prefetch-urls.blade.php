<?php 
	$url_type = $url_type ?? 'page';
?>
@foreach($prefetch_urls ?? [] as $prefetch_url)
    @switch($url_type)
        @case('page')
            <link rel="prefetch" href="{{ $prefetch_url }}" crossorigin="anonymous">
            @break
        @case('route')
            <link rel="prefetch" href="{{ route($prefetch_url) }}" crossorigin="anonymous">
            @break
        @default
            <link rel="prefetch" href="{{ asset($prefetch_url) }}" crossorigin="anonymous">
    @endswitch
@endforeach