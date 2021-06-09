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
                            <li class="nav-item" v-if="$auth.check()">
                                <router-link :to="{ name: 'dashboard' }" class="nav-link"
                                             href="{{ route('dashboard') }}">{{ __('menu.dashboard') }}</router-link>
                            </li>
                            <li class="nav-item">
                                <router-link :to="{ name: 'leaderboard' }" class="nav-link">
                                    {{__('menu.leaderboard')}}
                                </router-link>
                            </li>
                            <li class="nav-item">
                                <router-link :to="{ name: 'statuses.active'}" class="nav-link">
                                    {{__('menu.active')}}
                                </router-link>
                            </li>
                        </ul>
                        <ul class="navbar-nav w-auto" v-if="!$auth.check()">
                            <li class="nav-item">
                                <router-link :to="{ name: 'auth.login'}"
                                             class="nav-link">{{ __('menu.login') }}</router-link>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('menu.register') }}</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav w-auto" v-else>
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
                                    <span :v-="$auth.user().username"></span> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <router-link :to="{ name: 'profile', params: {username: $auth.user().username}}"
                                             class="dropdown-item">
                                    <i class="fas fa-user" aria-hidden="true"></i> {{ __('menu.profile') }}
                                </router-link>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-save" aria-hidden="true"></i> {{ __('menu.export') }}
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog" aria-hidden="true"></i> {{ __('menu.settings') }}
                                </a>
                                {{--                                    @if(Auth::user()->role >= 5)--}}
                                {{--                                        <a class="dropdown-item" href="#">--}}
                                {{--                                            <i class="fas fa-tools" aria-hidden="true"></i> {{__('menu.admin')}}--}}
                                {{--                                        </a>--}}
                                {{--                                    @endif--}}
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" @click.prevent="$auth.logout()">
                                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i> {{ __('menu.logout') }}
                                </a>
                    </div>
                    </li>
                    </ul>
                </div>
        </div>
        </nav>
        <app></app>
        </div>

        <script src="{{ mix('js/vue.js') }}"></script>

    </body>
</html>
