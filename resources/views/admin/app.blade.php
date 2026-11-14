<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Backend | {{ config('app.name') }}</title>

    <script>
        (function () {
            let mode = localStorage.getItem('darkMode') ?? 'auto';
            if (mode === 'auto') {
                mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', mode);
        })();
    </script>

    @vite(['resources/admin-app/app.ts'])

    <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}" />
</head>
<body>
    <div id="vue-admin-app"></div>
</body>
</html>
