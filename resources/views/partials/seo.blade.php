<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{$SNAIL_SEO_DESCRIPTION}}">
<meta name="keywords" content="{{$SNAIL_SEO_KEYWORDS}}">
<meta property="og:type" content="website" />
<meta property="og:locale" content="{{$SNAIL_SEO_LANGUAGE}}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:title" content="{{$SNAIL_SEO_TITLE}}" />
<meta property="og:url" content="{{request()->url()}}" />
<meta property="og:description" content="{{$SNAIL_SEO_DESCRIPTION}}" />
<meta property="og:image" content="{{ asset('img/placeholder.jpg') }}" />
<meta name="twitter:card" content="summary"></meta>
<meta name="twitter:title" content="{{$SNAIL_SEO_TITLE}}"></meta>
<meta name="twitter:description" content="{{$SNAIL_SEO_DESCRIPTION}}"></meta>
<meta name="twitter:image" content="{{ asset('img/placeholder.jpg') }}"></meta>
<meta name="theme-color" content="#f59e0b" />
<title>{{ $SNAIL_SEO_TITLE_FULL }}</title>
@yield('open-graph')
<link rel="favicon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
<link rel="shortcut icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
<?php
$_hreflangLocale = app()->getLocale();
$_hreflangSuffix = ltrim(substr(request()->path(), strlen($_hreflangLocale)), '/');
$_hreflangItUrl = url('it' . ($_hreflangSuffix ? '/' . $_hreflangSuffix : ''));
$_hreflangEnUrl = url('en' . ($_hreflangSuffix ? '/' . $_hreflangSuffix : ''));
?>
<link rel="alternate" hreflang="it" href="{{ $_hreflangItUrl }}" />
<link rel="alternate" hreflang="en" href="{{ $_hreflangEnUrl }}" />
<link rel="alternate" hreflang="x-default" href="{{ $_hreflangItUrl }}" />
