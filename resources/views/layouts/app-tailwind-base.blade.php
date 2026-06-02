<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ config('app.name', 'Träwelling') }}</title>

    @section('meta-description', __('about.block1'))
    @section('meta-robots', 'index')
    @section('canonical', route('static.welcome'))
    @include('layouts.includes.meta')


    <!-- Scripts -->
    <script>
        if (localStorage.getItem("darkMode") === null) {
            localStorage.setItem("darkMode", "auto");
        }
        let darkModeSetting = localStorage.getItem("darkMode");
        if (darkModeSetting === "auto") {
            darkModeSetting = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.classList.toggle("dark", darkModeSetting === "dark");
        document.documentElement.setAttribute("data-theme", darkModeSetting);
    </script>


    <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
    <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
    <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
    @vite('resources/css/welcome.css')
</head>
<body>
@yield('layout-content')
</body>
</html>
