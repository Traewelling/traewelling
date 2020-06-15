<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Admin | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-red navbar-dark">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <div class="brand-link">
            <img src="{{ asset('images/icons/touch-icon-vector.svg') }}" alt="{{ config('app.name') }} Logo"
                 class="brand-image" style="opacity: .8">
            <span class="brand-text font-weight-light">&nbsp;</span>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="pull-left image">
                    <img src="{{ route('account.showProfilePicture', ['username' => Auth::user()->username]) }}"
                         class="img-circle" alt="User Image">
                </div>
                <div class="info">
                    <p class="white-text">{{__('admin.greeting')}}, {{ Auth::user()->name }}!</p>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu"
                    data-accordion="false">
                    <!-- Add icons to the links using the .nav-icon class
                         with font-awesome or any other icon font library -->
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link {{ request()->is('admin') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                <!--
                    <li class="nav-item has-treeview {{ request()->is('admin/user*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('admin/user*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link {{ request()->is('admin/users') ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i> <p>List</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link {{ request()->is('admin/user') ? 'active' : '' }}">
                                    <i class="fas fa-plus nav-icon"></i> <p>Add</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    -->
                    <li class="nav-item has-treeview {{ request()->is('admin/event*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('admin/event*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Events <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">

                            <li class="nav-item">
                                <a href="{{ route('events.all') }}" class="nav-link {{ request()->is('admin/events') ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i>
                                    <p>List</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('events.newform') }}" class="nav-link {{ request()->is('admin/events/new') ? 'active' : '' }}">
                                    <i class="fas fa-plus nav-icon"></i> <p>{{ __('events.new') }}</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!--
                    <li class="nav-item has-treeview {{ request()->is('admin/camera*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('admin/camera*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-camera"></i>
                            <p>Cameras<i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link {{ request()->is('admin/cameras') ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i> <p>List</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link {{ request()->is('admin/camera') ? 'active' : '' }}">
                                    <i class="fas fa-plus nav-icon"></i> <p>Add</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    -->
                    <li class="nav-item">
                        <a href="{{url('/')}}" class="nav-link">
                            <i class="fas fa-arrow-circle-left nav-icon"></i>
                            <p>zurück</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">@yield('title')</h1>
                    </div>
                   <!-- <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Starter Page</li>
                        </ol>
                    </div>-->
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!--include('includes.messages')-->
                @yield('content')
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
        <div class="p-3">
            <h5>Title</h5>
            <p>Sidebar content</p>
        </div>
    </aside>
</div>
<script src="{{ asset('js/admin.js') }}"></script>
@yield('scripts')
</body>
</html>

