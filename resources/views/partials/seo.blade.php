<meta name="description" content="{{$SNAIL_SEO_DESCRIPTION}}">
<meta name="keywords" content="{{$SNAIL_SEO_KEYWORDS}}">
<meta property="og:type" content="website" />
<meta property="og:locale" content="en" />
<meta property="og:site_name" content="snappysnail.io" />
<meta property="og:title" content="{{$SNAIL_SEO_TITLE}}" />
<meta property="og:url" content="{{request()->url()}}" />
<meta property="og:description" content="{{$SNAIL_SEO_DESCRIPTION}}" />
<meta name="twitter:card" content="summary"></meta>
<meta name="twitter:title" content="{{$SNAIL_SEO_TITLE}}"></meta>
<meta name="twitter:description" content="{{$SNAIL_SEO_DESCRIPTION}}"></meta>
<meta name="theme-color" content="#fcbe03" />
<title>{{ $SNAIL_SEO_TITLE }}</title>
@yield('open-graph')
<link rel="favicon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
<link rel="shortcut icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
<link rel="canonical" href="{{url()->current()}}" />