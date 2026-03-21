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
        window.__adminUser = { // temp workaround to get roles, maybe make this besser in future (sorry. ~kris)
            roles: @json(auth()->user()?->getRoleNames() ?? []),
        };
    </script>

    @vite(['resources/tailwind-app/admin.ts'])

    <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}" />
</head>
<body>
    <div id="vue-admin-app"></div>
</body>
</html>
