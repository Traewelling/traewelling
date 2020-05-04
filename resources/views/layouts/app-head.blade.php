<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Mobile Webapp -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#c72730">
<meta name="theme-color" content="#c72730">

<!-- Search Engines -->
<meta name="name" content="{{ config('app.name') }}">
<meta name="description" content="{{__('about.block1')}}">
<meta name="keywords" content="Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon">
<meta name="robots" content="index, nofollow">

<link rel="author" href="/humans.txt">
<meta name="copyright" content="Träwelling Team">
<meta name="audience" content="Travellers">
<meta name="DC.Rights" content="Träwelling Team">
<meta name="DC.Description" content="{{__('about.block1')}}">
<meta name="DC.Language" content="de">

<!-- Icons -->
<link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
<link rel="shortcut favicon" rel="{{ asset('images/icons/favicon.ico') }}">
<link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
<link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">

<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/icons/touch-icon-ipad.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icons/touch-icon-iphone-retina.png') }}">
<link rel="apple-touch-icon" sizes="167x167" href="{{ asset('images/icons/touch-icon-ipad-retina.png') }}">

@yield('metadata')
