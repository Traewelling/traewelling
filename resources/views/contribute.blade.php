<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trans('contribute.title') }} - Träwelling</title>

    <!-- Dark Mode Script -->
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

    <!-- Fonts -->
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

    <!-- Favicons -->
    <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
    <link rel="icon" href="{{ asset('images/icons/logo128.png') }}" sizes="128x128">
    <link rel="icon" href="{{ asset('images/icons/logo512.png') }}" sizes="512x512">
    <link rel="shortcut icon" href="{{ asset('images/icons/favicon.ico') }}">

    <!-- Styles -->
    @vite(['resources/contribute-app/app.js'])
</head>
<body>
    <div id="contribute-app"></div>
</body>
</html>
