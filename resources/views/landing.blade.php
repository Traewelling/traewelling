<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>

        <!-- Scripts -->

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <link rel="author" href="/humans.txt">
        <meta name="copyright" content="Träwelling Team">
        <meta name="description" content="{{__('about.block1')}}">
        <meta name="keywords" content="Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon">
        <meta name="audience" content="Travellers">
        <meta name="DC.Rights" content="Träwelling Team">
        <meta name="DC.Description" content="{{__('about.block1')}}">
        <meta name="DC.Language" content="de">

    </head>
    <body>

        <div id="app">
            <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Träwelling') }}
                    </a>
                    <div class="navbar-toggler">
                        @auth
                            <button class="navbar-toggler notifications-board-toggle" type="button"
                                    data-mdb-toggle="modal"
                                    data-mdb-target="#notifications-board" aria-controls="navbarSupportedContent"
                                    aria-expanded="false"
                                    aria-label="{{ __('Show notifications') }}">
                                <span class="notifications-bell far fa-bell"></span>
                                <span class="notifications-pill badge rounded-pill badge-notification" hidden>0</span>
                            </button>
                        @endauth
                        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse"
                                data-mdb-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto">
                            @auth
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('dashboard/*') ? 'active' : '' }}"
                                       href="{{ route('dashboard') }}">{{ __('menu.dashboard') }}</a>
                                </li>
                            @endauth
                            <li class="nav-item">
                                <router-link :to="{ name: 'leaderboard' }" class="nav-link">
                                    __('menu.leaderboard')
                                </router-link>
                            </li>
                            <li class="nav-item">
                                <router-link :to="{ name: 'statuses.active'}" class="nav-link">
                                    __('menu.active')
                                </router-link>
                            </li>
                        </ul>
                        <ul class="navbar-nav w-auto">
                            @guest
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('menu.login') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('menu.register') }}</a>
                                </li>
                            @else
                                <form class="form-inline" action="{{ route('userSearch') }}">
                                    <div class="input-group md-form form-sm form-2 ps-0 m-0">
                                        <input name="searchQuery" type="text"
                                               class="border border-white rounded-left form-control my-0 py-1"
                                               placeholder="{{ __('stationboard.submit-search') }}"
                                               aria-label="User suchen"/>
                                        <button class="input-group-text btn-primary" type="submit">
                                            <i class="fas fa-search" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </form>
                                <li class="nav-item d-none d-md-inline-block">
                                    <a href="javascript:void(0)" id="notifications-toggle"
                                       class="nav-link notifications-board-toggle"
                                       data-mdb-toggle="modal"
                                       data-mdb-target="#notifications-board">
                                        <span class="notifications-bell far fa-bell"></span>
                                        <span class="notifications-pill badge rounded-pill badge-notification"
                                              hidden>0</span>
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" href="#" class="nav-link dropdown-toggle mdb-select"
                                       role="button" data-mdb-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="false">
                                        {{ Auth::user()->name }} <span class="caret"></span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ url('profile/'.Auth::user()->username) }}">
                                            <i class="fas fa-user"></i> {{ __('menu.profile') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('export.landing') }}">
                                            <i class="fas fa-save"></i> {{ __('menu.export') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('settings') }}">
                                            <i class="fas fa-cog"></i> {{ __('menu.settings') }}
                                        </a>
                                        @if(Auth::user()->role >= 5)
                                            <a class="dropdown-item" href="{{route('admin.dashboard')}}">
                                                <i class="fas fa-tools"></i> {{__('menu.admin')}}
                                            </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item" form="logout-form" type="submit">
                                            <i class="fas fa-sign-out-alt"></i> {{ __('menu.logout') }}
                                        </button>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                              style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
            <app></app>
        </div>

        <script src="{{ mix('js/vue.js') }}"></script>

    </body>
</html>
