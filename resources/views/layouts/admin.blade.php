<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta http-equiv="x-ua-compatible" content="ie=edge"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>

        <title>@yield('title') | Admin | {{ config('app.name') }}</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
    </head>
    <body class="hold-transition sidebar-mini">
        <div class="wrapper">
            <nav class="main-header navbar navbar-expand navbar-red navbar-dark">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#"><em class="fas fa-bars"></em></a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            <em class="fas fa-sign-out-alt"></em>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>

            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <div class="brand-link">
                    <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                         class="brand-image" style="opacity: .8">
                    <span class="brand-text font-weight-light">&nbsp;</span>
                </div>

                <div class="sidebar">
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="pull-left image">
                            <img src="{{ route('account.showProfilePicture', ['username' => Auth::user()->username]) }}"
                                 class="img-circle" alt="User Image"/>
                        </div>
                        <div class="info">
                            <p class="text-white">{{__('admin.greeting')}}, {{ Auth::user()->name }}!</p>
                        </div>
                    </div>

                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu"
                            data-accordion="false">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}"
                                   class="nav-link {{ request()->is('admin') ? 'active' : '' }}">
                                    <em class="nav-icon fas fa-tachometer-alt"></em>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item has-treeview {{ request()->is('admin/event*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/event*') ? 'active' : '' }}">
                                    <em class="nav-icon fas fa-calendar-alt"></em>
                                    <p>Veranstaltungen <em class="right fas fa-angle-left"></em></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.events') }}"
                                           class="nav-link {{ request()->is('admin/events') ? 'active' : '' }}">
                                            <em class="fas fa-list nav-icon"></em>
                                            <p>List</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('events.newform') }}"
                                           class="nav-link {{ request()->is('admin/events/new') ? 'active' : '' }}">
                                            <em class="fas fa-plus nav-icon"></em>
                                            <p>{{ __('events.new') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.events.suggestions') }}"
                                           class="nav-link {{ request()->is('admin.events.suggestions') ? 'active' : '' }}">
                                            <em class="far fa-calendar-plus nav-icon"></em>
                                            <p>Vorschläge</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="{{url('/')}}" class="nav-link">
                                    <em class="fas fa-arrow-circle-left nav-icon"></em>
                                    <p>zurück</p>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>

            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0 text-dark">@yield('title')</h1>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content">
                    <div class="container-fluid">
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
                                            <a href="#" class="close" data-dismiss="alert"
                                               aria-label="close">&times;</a>
                                        </p>
                                        <hr/>
                                    </div>
                                </div>
                            @endif
                            {{ Session::forget('alert-' . $msg) }}
                        @endforeach

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/admin.js') }}"></script>
        @yield('scripts')
    </body>
</html>
