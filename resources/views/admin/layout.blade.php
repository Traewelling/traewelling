<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>

        <title>@yield('title') | Admin | {{ config('app.name') }}</title>

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
        <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}"/>
        <script src="{{ asset('js/admin.js') }}"></script>
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{route('admin.dashboard')}}">
                    <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                         class="brand-image me-3" style="width: 30px; opacity: 0.8">
                    TRWL Admin
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false"
                        aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            Dashboard
                        </a>
                        <a class="nav-link" href="{{ route('admin.events') }}">
                            Veranstaltungen
                        </a>
                        <a class="nav-link" href="{{ route('admin.status') }}">
                            Status
                        </a>
                        <a class="nav-link" href="{{ route('admin.stationboard') }}">
                            Checkin
                        </a>
                        <a class="nav-link" href="{{ route('admin.users') }}">
                            Users
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <div class="container-fluid">
                @hasSection('title')
                    <h1 class="mt-3 mb-3 text-dark" id="pageTitle">@yield('title')</h1>
                @endif
                @if ($errors->any())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger">
                                <h2 class="text-alert">Es sind Fehler aufgetreten:</h2>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                        <div class="row">
                            <div class="col-md-12">
                                <p class="alert alert-{{ $msg }}">
                                    {!! Session::get('alert-' . $msg) !!}
                                </p>
                                <hr/>
                            </div>
                        </div>
                    @endif
                    {{ Session::forget('alert-' . $msg) }}
                @endforeach

                @yield('content')
            </div>
        </main>
        @yield('scripts')
    </body>
</html>
