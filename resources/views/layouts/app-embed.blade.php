<!DOCTYPE html>
@php
    use App\Http\Controllers\Backend\VersionController;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-bs-theme="dark">
<head>
    <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>
    <base target="_parent">

    @include('layouts.includes.meta')

    <!-- Scripts -->
    <!-- Run this blocking script as early as possible to prevent flickering -->
    <script>
        if (localStorage.getItem("darkMode") === null) {
            localStorage.setItem("darkMode", "auto");
        }
        let darkModeSetting = localStorage.getItem("darkMode");
        if (darkModeSetting === "auto") {
            darkModeSetting = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.classList.toggle("dark", darkModeSetting === "dark");
        document.documentElement.setAttribute("data-bs-theme", darkModeSetting);
    </script>

    <!-- Fonts -->
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

    <!-- Styles -->
    <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
    <link rel="icon" href="{{ asset('images/icons/logo128.png') }}" sizes="128x128">
    <link rel="icon" href="{{ asset('images/icons/logo512.png') }}" sizes="512x512">
    <link rel="shortcut icon" href="{{ asset('images/icons/favicon.ico') }}">
    <link rel="author" href="/humans.txt">

    @vite(['resources/sass/app.scss', 'resources/sass/app-dark.scss', 'resources/js/app.js'])

    <style>
        html { overflow: hidden; }
        body { background: transparent; margin: 0; }
        /* Negative top margins are used to overlap with the navbar in the full layout.
           In the embed layout there is no navbar, so they push content above the viewport. */
        .mt-n4, .mt-n5 { margin-top: 0 !important; }
        .fab-container { display: none !important; }
    </style>

    @yield('head')
</head>
<body>
<div id="vue-app">
    @include('includes.message-block')
    @yield('content')
</div>

<script>
    var token = '{{ csrf_token() }}';
    var mapprovider = '{{ Auth::user()->mapprovider ?? "default" }}';

    // Report content height to parent frame for auto-sizing
    (function() {
        function reportHeight() {
            var height = Math.max(
                document.body.scrollHeight,
                document.body.offsetHeight,
                document.documentElement.scrollHeight,
                document.documentElement.offsetHeight
            );
            window.parent.postMessage({ type: 'trwl-embed-resize', height: height }, '*');
        }

        // Report on load
        window.addEventListener('load', reportHeight);

        // Report on DOM changes
        var observer = new MutationObserver(reportHeight);
        observer.observe(document.body, { childList: true, subtree: true, attributes: true });

        // Report on resize
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(reportHeight).observe(document.body);
        }

        // Intercept JavaScript navigation (window.location = ...) and redirect parent instead
        if (window.parent !== window) {
            // Navigation API (Chromium): intercept before navigation happens
            if (window.navigation) {
                window.navigation.addEventListener('navigate', function(event) {
                    if (!event.canIntercept || event.hashChange) return;
                    try {
                        var url = new URL(event.destination.url);
                        if (url.origin === window.location.origin && !url.pathname.startsWith('/embed/')) {
                            event.preventDefault();
                            window.parent.location.href = url.pathname + url.search + url.hash;
                        }
                    } catch(e) {}
                });
            }
        }

        // Listen for dark mode sync from parent
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'trwl-embed-darkmode') {
                var mode = event.data.mode;
                localStorage.setItem('darkMode', mode);

                var resolved = mode;
                if (resolved === 'auto') {
                    resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.classList.toggle('dark', resolved === 'dark');
                document.documentElement.setAttribute('data-bs-theme', resolved);
            }
        });
    })();
</script>
@stack('scripts')
@yield('footer')
</body>
</html>
