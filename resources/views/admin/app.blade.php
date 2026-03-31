<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Backend | {{ config('app.name') }}</title>

    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', saved ?? (prefersDark ? 'dark' : 'light'));
        })();
    </script>

    @vite(['resources/admin-app/app.ts'])

    <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}" />
</head>
<body>
    <div id="vue-admin-app"></div>
</body>
</html>
