<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>

<meta name="csrf-token" content="{{ csrf_token() }}"/>

<meta name="copyright" content="Träwelling Team"/>
<meta name="audience" content="Travellers"/>
<meta name="DC.Rights" content="Träwelling Team"/>
<meta name="DC.Language" content="{{ str_replace('_', '-', app()->getLocale()) }}"/>

<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="#c72730"/>
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/icons/touch-icon-ipad.png') }}"/>
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icons/touch-icon-iphone-retina.png') }}"/>
<link rel="apple-touch-icon" sizes="167x167" href="{{ asset('images/icons/touch-icon-ipad-retina.png') }}"/>

<meta name="mobile-web-app-capable" content="yes"/>
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#c72730"/>
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#811a0e"/>
<meta name="name" content="{{ config('app.name') }}"/>

@hasSection('canonical')
    <link rel="canonical" href="@yield('canonical')"/>
@endif

@hasSection('meta-robots')
    <meta name="robots" content="@yield('meta-robots')"/>
@endif
@hasSection('meta-description')
    <meta name="description" content="@yield('meta-description')"/>
    <meta name="DC.Description" content="@yield('meta-description')"/>
@endif
