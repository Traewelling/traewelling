<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>

        <title>@hasSection('title')
                @yield('title') |
            @endif Backend | {{ config('app.name') }}</title>

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
        <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}"/>
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-dark" id="navbar-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{route('admin.dashboard')}}">
                    <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                         class="brand-image me-3" style="width: 30px; opacity: 0.8">
                    TRWL Backend
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false"
                        aria-label="{{ __('toggle-navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav me-auto">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <i class="fa-solid fa-table-columns"></i>
                            Dashboard
                        </a>
                        @if(auth()->user()->hasRole('admin'))
                            <a class="nav-link" href="{{ route('admin.users') }}">
                                <i class="fa-solid fa-users"></i>
                                Users
                            </a>
                        @endif

                        @if(auth()->user()->can('view-events') || auth()->user()->hasRole('admin'))
                            <a class="nav-link" href="{{ route('admin.events') }}">
                                <i class="fa-solid fa-calendar"></i>
                                Events
                            </a>
                        @endif

                        @if(auth()->user()->hasRole('admin'))
                            <a class="nav-link" href="{{ route('admin.status') }}">
                                <i class="fa-solid fa-broadcast-tower"></i>
                                Status
                            </a>
                            <a class="nav-link" href="{{ route('admin.stationboard') }}">
                                <i class="fa-solid fa-train"></i>
                                Checkin
                            </a>
                            <a class="nav-link" href="{{ route('admin.trip.create') }}">
                                <i class="fa-solid fa-plus"></i>
                                Trips
                            </a>
                            <a class="nav-link" href="{{ route('admin.stations') }}">
                                <i class="fa-solid fa-map-marker"></i>
                                Stations
                            </a>
                            <a class="nav-link" href="{{ route('admin.activity') }}">
                                <i class="fa-solid fa-hammer"></i>
                                Activity
                            </a>
                            <a class="nav-link" href="{{ route('admin.reports') }}">
                                <i class="fa-solid fa-flag"></i>
                                Reports
                            </a>
                        @endif
                    </div>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-home"></i>
                                Back to Träwelling
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <main class="container-fluid pt-3">
            @hasSection('title')
                <h1 class="mb-3 text-white fs-3" id="pageTitle">@yield('title')</h1>
            @endif
            @if ($errors->any())
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <h2 class="text-alert">Some errors occurred:</h2>
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
        </main>
        @yield('scripts')
    </body>
</html>
