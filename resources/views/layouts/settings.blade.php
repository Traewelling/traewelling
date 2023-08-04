<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>{{__('menu.settings')}} - {{ config('app.name', 'Träwelling') }}</title>

        @include('layouts.includes.meta')

        <!-- Scripts -->
        <!-- Run this blocking script as early as possible to prevent flickering -->
        <script>
            if (localStorage.getItem("darkMode") === null) {
                localStorage.setItem("darkMode", "auto");
            }
            var darkModeSetting = localStorage.getItem("darkMode");
            if (darkModeSetting === "auto") {
                darkModeSetting = window.matchMedia("(prefers-color-scheme: dark)")
                    .matches
                    ? "dark"
                    : "light";
            }
            if (darkModeSetting === "dark") {
                document.documentElement.classList.add("dark");
            } else {
                document.documentElement.classList.remove("dark");
            }
        </script>

        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
        <link href="{{ mix('css/app-dark.css') }}" rel="stylesheet">
        <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <link rel="author" href="/humans.txt">
        <link rel="manifest" href="/manifest.json"/>

        <script src="{{ mix('js/app.js') }}"></script>

        @yield('head')

        <style>
          body {
            font-size: .875rem;
          }

          .feather {
            width: 16px;
            height: 16px;
          }

          /*
           * Sidebar
           */

          .sidebar {
            position: fixed;
            top: 0;
            /* rtl:raw:
            right: 0;
            */
            bottom: 0;
            /* rtl:remove */
            left: 0;
            z-index: 100; /* Behind the navbar */
            padding: 48px 0 0; /* Height of navbar */
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
          }

          @media (max-width: 767.98px) {
            .sidebar {
              top: 5rem;
            }
          }

          .sidebar-sticky {
            height: calc(100vh - 48px);
            overflow-x: hidden;
            overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
          }

          .sidebar .nav-link {
            font-weight: 500;
            color: #333;
          }

          .sidebar .nav-link .feather {
            margin-right: 4px;
            color: #727272;
          }

          .sidebar .nav-link.active {
            color: #2470dc;
          }

          .sidebar .nav-link:hover .feather,
          .sidebar .nav-link.active .feather {
            color: inherit;
          }

          .sidebar-heading {
            font-size: .75rem;
          }

          /*
           * Navbar
           */

          .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
          }

          .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
          }

          .navbar .form-control {
            padding: .75rem 1rem;
          }

          .form-control-dark {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .1);
          }

          .form-control-dark:focus {
            border-color: transparent;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
          }


          .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
          }

          @media (min-width: 768px) {
            .bd-placeholder-img-lg {
              font-size: 3.5rem;
            }
          }

          .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
          }

          .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
          }

          .bi {
            vertical-align: -.125em;
            fill: currentColor;
          }

          .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
          }

          .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
          }

        </style>
    </head>
    <body>
        <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
            <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="/">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>&nbsp;Zurück zu Träwelling
            </a>
            <button class="navbar-toggler d-md-none collapsed" type="button"
                    data-mdb-toggle="collapse" data-mdb-target="#sidebarMenu" aria-controls="sidebarMenu"
                    aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </header>

        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-body sidebar collapse">
                    <div class="position-sticky pt-3 sidebar-sticky">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="{{route('settings.profile')}}">
                                    <i class="fas fa-user" aria-hidden="true"></i>
                                    {{ __('settings.title-profile') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.privacy')}}">
                                    <i class="fas fa-user-secret" aria-hidden="true"></i>
                                    {{ __('menu.privacy') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.follower')}}">
                                    <i class="fas fa-users" aria-hidden="true"></i>
                                    {{ __('menu.settings.myFollower') }}
                                </a>
                            </li>
                            @if(auth()->user()->mutedUsers->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('settings.mutes')}}">
                                        <i class="fas fa-ban" aria-hidden="true"></i>
                                        {{ __('user.muted.heading2') }}
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->blockedUsers->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('settings.blocks')}}">
                                        <i class="fas fa-ban" aria-hidden="true"></i>
                                        {{ __('user.blocked.heading2') }}
                                    </a>
                                </li>
                            @endif
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
                                <span>Security</span>
                            </h6>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.login-providers')}}">
                                    <i class="fas fa-plug" aria-hidden="true"></i>
                                    {{ __('settings.title-loginservices') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.sessions')}}">
                                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                                    {{ __('settings.title-sessions') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.ics')}}">
                                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                    {{ __('settings.title-ics') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.tokens')}}">
                                    <i class="fas fa-shapes" aria-hidden="true"></i>
                                    {{ __('settings.title-tokens') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.webhooks')}}">
                                    <i class="fas fa-shapes" aria-hidden="true"></i>
                                    {{ __('settings.title-webhooks') }}
                                </a>
                            </li>
                        </ul>

                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
                            <span>Extra</span>
                        </h6>
                        <ul class="nav flex-column mb-2">
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('settings.account')}}">
                                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                                    {{ __('settings.tab.account') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('dev.apps')}}">
                                    <i class="fas fa-flask"></i>
                                    {{ __('settings.title-appdevelopment') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-7">
                                @include('includes.message-block')

                                @yield('content')
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script>
            var token            = '{{ csrf_token() }}';
            var urlDisconnect    = '{{ route('provider.destroy') }}';
        </script>
    </body>
</html>
