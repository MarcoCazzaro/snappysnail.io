<?php
	$seo_title = "Snappynsail";
	$seo_description = "Snappynsail is a web development company";
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{$seo_description}}">
<meta name="keywords" content="web development,websites,siti web,we">
<meta property="og:type" content="website" />
<meta property="og:locale" content="en" />
<meta property="og:site_name" content="snappysnail.io" />
<meta property="og:title" content="{{$seo_title}}" />
<meta property="og:url" content="{{request()->url()}}" />
<meta property="og:description" content="{{$seo_description}}" />
<meta name="twitter:card" content="summary"></meta>
<meta name="twitter:title" content="{{$seo_title}}"></meta>
<meta name="twitter:description" content="{{$seo_description}}"></meta>
<meta name="theme-color" content="#fcbe03" />
<title>{{ $seo_title }}</title>
@yield('open-graph')
<link rel="favicon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
<link rel="shortcut icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>