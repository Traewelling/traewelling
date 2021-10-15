<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>

        <title>@yield('title') | Admin | {{ config('app.name') }}</title>
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
        <script src="{{ asset('js/admin.js') }}"></script>

        <style>
            #wrapper {
                display: flex;
                width: 100%;
                align-items: stretch;
            }
        </style>
    </head>
    <body>
        <div id="wrapper">
            <nav class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;">
                <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                         class="brand-image me-3" style="width: 30px; opacity: 0.8">
                    <span class="fs-4">TRWL Admin</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="/" class="nav-link">
                            <i class="bi bi-skip-backward me-2"></i>
                            Zurück zu Träwelling
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link text-white {{ request()->is('admin') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.events')}}"
                           class="nav-link text-white {{ request()->is('admin.events') ? 'active' : '' }}">
                            <i class="bi bi-calendar2-event"></i>
                            Veranstaltungen
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.status') }}"
                           class="nav-link text-white {{ request()->is('admin.status') ? 'active' : '' }}">
                            <i class="bi bi-person-rolodex"></i>
                            Status
                        </a>
                    </li>
                </ul>
                <hr>
                <span class="d-flex align-items-center text-white text-decoration-none">
                        <img src="{{ route('account.showProfilePicture', ['username' => auth()->user()->username]) }}"
                             alt="" width="32" height="32" class="rounded-circle me-2">
                    <strong>{{auth()->user()->name}}</strong>
                </span>
            </nav>
            <main style="width: calc(100%  - 280px)">
                <div class="container-fluid">
                    @hasSection('title')
                        <h1 class="mt-3 mb-3 text-dark">@yield('title')</h1>
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
        </div>
        @yield('scripts')
    </body>
</html>
