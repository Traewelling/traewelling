<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>@yield('title') | Träwelling</title>
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet"/>
        @vite('resources/sass/app.scss')
        <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}"/>
    </head>
    <body>
        <div id="app">
            <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
                <div class="container">
                    <a class="navbar-brand" href="javascript:void(0)">
                        Träwelling
                    </a>
                </div>
            </nav>

            <main class="py-4">
                <div class="container">
                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>
