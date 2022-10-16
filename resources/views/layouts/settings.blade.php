<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>

        @include('layouts.includes.meta')

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}"></script>

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ mix('css/admin.css') }}" rel="stylesheet">
        <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <link rel="author" href="/humans.txt">
        <link rel="manifest" href="/manifest.json"/>

        @yield('head')
    </head>
    <body>
        @include('includes.message-block')
        <main class="bg-dark">
            <div class="d-flex flex-column col-3 d-none d-md-flex"></div>

            <div class="d-flex flex-column flex-shrink-0 p-2 text-white bg-dark" style="max-width: 280px;">
                <a href="{{ route('dashboard') }}"
                   class="justify-content-center mb-3 mx-auto text-white text-decoration-none">
                    <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                         class="brand-image me-3" style="width: 110px">
                </a>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-muted">
                            <i class="fas fa-backward me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">Zurück zu Träwelling</span>
                        </a>
                    </li>
                    <!--
                    <li>
                        <a href="#"
                           class="nav-link text-white {{ request()->is('admin') ? 'active' : '' }}">
                            <i class="fas fa-user me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">Profil</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="nav-link text-white {{ request()->is('admin/events*') ? 'active' : '' }}">
                            <i class="fas fa-lock me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">Privacy</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="nav-link text-white {{ request()->is('admin/status*') ? 'active' : '' }}">
                            <i class="fas fa-lock me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">Zugänge</span>
                        </a>
                    </li>
                    -->
                    <li>
                        <a href="{{ route('dev.apps') }}"
                           class="nav-link text-white {{ request()->is('settings/applications*') ? 'active' : '' }}">
                            <i class="fas fa-code me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">Develop <span
                                    class="badge text-bg-warning">beta</span></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('l5-swagger.default.api') }}" target="_blank"
                           class="nav-link text-white ms-2 {{ request()->is('settings/applications*') ? '' : 'd-none' }}">
                            <i class="fas fa-flask me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">API Docs <span
                                    class="badge text-bg-danger">incomplete</span></span></span>
                        </a>
                    </li>
                    <hr>
                    <li>
                        <a href="#"
                           class="nav-link text-white {{ request()->is('admin/api/usage*') ? 'active' : '' }}">
                            <i class="fa-solid fa-sign-out-alt me-2" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="container-fluid bg-light px-5 pt-4 bg-light-gray" style="overflow-y: scroll !important;">
                <div class="row">
                    <div class="col-12 col-lg-10 col-xl-8 ">
                        <div class="my-4">
                             <span class="float-end">
                                @yield('additional-content-end')
                            </span>
                            <h4 class="mb-0 mt-5">@yield('title')</h4>
                            <p>@yield('subtitle')</p>

                            <hr class="my-4"/>
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>

    @include('includes.modals.notifications-board')
    @yield('footer')
</html>
