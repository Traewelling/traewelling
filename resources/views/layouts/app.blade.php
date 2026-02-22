<!DOCTYPE html>
@php
    use App\Http\Controllers\Backend\VersionController;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-bs-theme="dark">
<head>
    <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>

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
    <link rel="manifest" href="/manifest.json">

    @vite(['resources/sass/app.scss', 'resources/sass/app-dark.scss', 'resources/js/app.js'])

    @yield('head')
</head>
<body>
<div id="vue-app">
    <vue-layout>
        @include('includes.message-block')
        @yield('content')
    </vue-layout>
</div>
<div class="alert text-center cookiealert" role="alert">
    <b>Do you like cookies?</b> &#x1F36A; {{ __('messages.cookie-notice') }}
    <a href="{{ route('legal.privacy') }}">{{ __('messages.cookie-notice-learn') }}</a>
    <button type="button" class="btn btn-primary btn-sm acceptcookies"
            aria-label="{{ __('messages.cookie-notice-button') }}">
        {{ __('messages.cookie-notice-button') }}
    </button>
</div>

<script>
    var token = '{{ csrf_token() }}';
    var mapprovider = '{{ Auth::user()->mapprovider ?? "default" }}';
</script>
@stack('scripts')
@yield('footer')
</body>
</html>
