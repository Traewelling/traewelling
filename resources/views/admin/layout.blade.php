<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>

        <title>@yield('title') | Admin | {{ config('app.name') }}</title>
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
        <script src="{{ asset('js/admin.js') }}"></script>

    </head>

    <body>
        <main>
            <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;">
                <a href="{{ route('admin.dashboard') }}"
                   class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                         class="brand-image me-3" style="width: 30px; opacity: 0.8">
                    <span class="fs-4">TRWL Admin</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-muted">
                            <i class="fas fa-backward me-2" aria-hidden="true"></i>
                            Zurück zu Träwelling
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link text-white {{ request()->is('admin') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt me-2" aria-hidden="true"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.events')}}"
                           class="nav-link text-white {{ request()->is('admin/events*') ? 'active' : '' }}">
                            <i class="fas fa-calendar me-2" aria-hidden="true"></i>
                            Veranstaltungen
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.status') }}"
                           class="nav-link text-white {{ request()->is('admin/status*') ? 'active' : '' }}">
                            <i class="fas fa-train me-2" aria-hidden="true"></i>
                            Status
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.stationboard') }}"
                           class="nav-link text-white {{ request()->is('admin/checkin*') ? 'active' : '' }}">
                            <i class="fas fa-ticket-alt me-2" aria-hidden="true"></i>
                            Checkin
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users') }}"
                           class="nav-link text-white {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <i class="fas fa-users me-2" aria-hidden="true"></i>
                            Users
                        </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                       id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl(auth()->user()) }}"
                             alt="" width="32" height="32" class="rounded-circle me-2">
                        <strong>{{auth()->user()->name}}</strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li>
                            <a class="dropdown-item" href="{{ url('profile/'.Auth::user()->username) }}">
                                <i class="fas fa-user" aria-hidden="true"></i> {{ __('menu.profile') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('settings') }}">
                                <i class="fas fa-cog" aria-hidden="true"></i> {{ __('menu.settings') }}
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button class="dropdown-item" form="logout-form" type="submit">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i> {{ __('menu.logout') }}
                            </button>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                  style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="container-fluid bg-light px-5 pt-4" style="overflow-y: scroll !important;">
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
        @yield('scripts')
    </body>
</html>
