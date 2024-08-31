@php use App\Http\Controllers\Backend\VersionController; @endphp
    <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>{{ config('app.name', 'Träwelling') }}</title>

        @section('meta-description', __('about.block1'))
        @section('meta-robots', 'index')
        @section('canonical', route('static.welcome'))
        @include('layouts.includes.meta')

        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
        @vite('resources/css/welcome.css')
    </head>
    <body>
        <div id="__next">
            <div class="drawer"><input id="my-drawer-3" type="checkbox" class="drawer-toggle">
                <div class="drawer-content flex flex-col">
                    <div class="overflow-y-auto flex flex-col">
                        <div class="w-full flex justify-center shadow-lg">
                            <div class="navbar py-2 bg-base-100 max-w-6xl">
                                <div class="navbar-start">
                                    <div class="flex-none lg:hidden">
                                        <label for="my-drawer-3" class="btn btn-square btn-ghost">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                                 class="h-5 inline-block w-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
                                            </svg>
                                        </label>
                                    </div>
                                    <div class="md:flex-1 flex-none px-2 mx-2">
                                        <a href="/">
                                            <span class="font-bold text-xl">
                                                <img class="mask inline-block mr-2 mask-circle w-12"
                                                     alt="Träwelling Logo"
                                                     src="{{ asset('images/icons/logo512.png') }}">
                                                {{ config('app.name', 'Träwelling') }}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div class="navbar-center hidden lg:flex">
                                    <ul class="menu menu-horizontal custom-menu">
                                        @include('welcome.partials.nav-links')
                                    </ul>
                                </div>
                                <div class="navbar-end hidden lg:flex">
                                    <a href="{{ route('register') }}">
                                        <button
                                            class="btn md:mt-0 mt-4 btn-block btn-sm text-xs btn-outline normal-case hover:text-white hover:btn-primary">
                                            {{ __('welcome.get-on-board') }}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-y-auto">
                            <main>
                                <div class="hero pt-4 md:pt-12 pb-12 bg-gradient-to-t from-blue-500 to-purple-700">
                                    <div class="hero-content flex-col lg:flex-row-reverse w-screen lg:ps-28">
                                        <div class="lg:w-3/5">
                                            <h1 class="text-5xl text-slate-100 font-bold md:leading-none leading-tight mt-0">
                                                <span class="sm:block md:inline">Hop in. </span>
                                                <span class="sm:block md:inline">Check&nbsp;in. </span>
                                                <span class="sm:block md:inline">#Träwelling.</span>
                                            </h1>
                                            <p class="py-2 text-xl text-slate-100 mt-4 pr-12">
                                                {{ __('welcome.header.track') }}
                                                <span class="md:block">{{ __('welcome.header.vehicles') }}</span>
                                            </p>
                                            <p class="py-2 text-xl text-slate-100 mt-4 pr-12">
                                                {{ __('welcome.header.open-source') }}
                                            </p>
                                        </div>
                                        <div class="w-full lg:w-2/5 rounded-box bg-base-200 p-6 max-w-md">
                                            @include('welcome.partials.login')
                                        </div>
                                    </div>
                                </div>
                                <div class="grid place-items-center bg-slate-50 w-full">
                                    <div class="max-w-6xl w-full py-24 px-4 content-center justify-center">
                                        <div class="stats flex flex-col lg:flex-row bg-base-100 shadow-lg">
                                            <div class="stat">
                                                <div class="stat-figure text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                         class="inline-block w-8 h-8 stroke-current"
                                                    >
                                                        <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path fill="currentColor"
                                                              d="M512 96c0 50.2-59.1 125.1-84.6 155c-3.8 4.4-9.4 6.1-14.5 5L320 256c-17.7 0-32 14.3-32 32s14.3 32 32 32l96 0c53 0 96 43 96 96s-43 96-96 96l-276.4 0c8.7-9.9 19.3-22.6 30-36.8c6.3-8.4 12.8-17.6 19-27.2L416 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0c-53 0-96-43-96-96s43-96 96-96l39.8 0c-21-31.5-39.8-67.7-39.8-96c0-53 43-96 96-96s96 43 96 96zM117.1 489.1c-3.8 4.3-7.2 8.1-10.1 11.3l-1.8 2-.2-.2c-6 4.6-14.6 4-20-1.8C59.8 473 0 402.5 0 352c0-53 43-96 96-96s96 43 96 96c0 30-21.1 67-43.5 97.9c-10.7 14.7-21.7 28-30.8 38.5l-.6 .7zM128 352a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM416 128a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/>
                                                    </svg>
                                                </div>
                                                <div class="stat-value text-primary">
                                                    {{ round($stats->distance/1000/1000/1000,1) }}
                                                    {{ __('welcome.stats.million') }}
                                                </div>
                                                <div class="stat-desc">{{ __('welcome.stats.distance') }}</div>
                                            </div>
                                            <div class="stat">
                                                <div class="stat-figure text-primary-focus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"
                                                         class="inline-block w-8 h-8 stroke-primary-focus">
                                                        <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path fill="currentColor"
                                                              d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192l42.7 0c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0L21.3 320C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7l42.7 0C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3l-213.3 0zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352l117.3 0C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7l-330.7 0c-14.7 0-26.7-11.9-26.7-26.7z"/>
                                                    </svg>
                                                </div>
                                                <div class="stat-value text-primary-focus">
                                                    {{ $stats->user_count }}
                                                </div>
                                                <div class="stat-desc">{{ __('welcome.stats.registered') }}</div>
                                            </div>
                                            <div class="stat">
                                                <div class="stat-figure text-secondary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                         class="inline-block w-8 h-8 stroke-primary-focus">
                                                        <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path fill="currentColor"
                                                              d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                                                    </svg>
                                                </div>
                                                <div
                                                    class="stat-value text-secondary">{{ round($stats->duration/60/60/24/365, 1) }}
                                                </div>
                                                <div class="stat-desc">{{ __('welcome.stats.duration') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="my-10 bg-base-200">
                                    <div class="hero min-h-full rounded-lg">
                                        <div class="hero-content flex-col lg:flex-row xl:gap-10">
                                            <img alt="hero"
                                                 loading="lazy"
                                                 src="{{ asset('images/welcome/map.png') }}"
                                                 class="h-80 w-full object-cover rounded-lg shadow-lg">
                                            <div class="w-full">
                                                <h1 class="text-3xl font-bold">{{ __('welcome.hero.map.title') }}</h1>
                                                <p class="py-6">
                                                    {{ __('welcome.hero.map.description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hero min-h-full rounded-lg">
                                        <div class="hero-content flex-col lg:flex-row-reverse xl:gap-10">
                                            <img alt="hero"
                                                 loading="lazy"
                                                 src="{{ asset('images/welcome/stats.png') }}"
                                                 class="h-80 w-full object-cover rounded-lg shadow-lg">
                                            <div class="w-full">
                                                <h1 class="text-3xl font-bold">{{ __('welcome.hero.stats.title') }}</h1>
                                                <p class="py-6">
                                                    {{ __('welcome.hero.stats.description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hero min-h-full rounded-lg">
                                        <div class="hero-content flex-col lg:flex-row xl:gap-10">
                                            <img alt="hero"
                                                 loading="lazy"
                                                 src="{{ asset('images/welcome/mobile-mockup.png') }}"
                                                 class="h-80 w-full object-cover rounded-lg shadow-lg">
                                            <div class="w-full">
                                                <h1 class="text-3xl font-bold">{{ __('welcome.hero.mobile.title') }}</h1>
                                                <p class="py-6">
                                                    {{ __('welcome.hero.mobile.description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid place-items-center bg-slate-50 w-full border-b-2">
                                    <div class="max-w-6xl py-24 px-4 content-center text-center justify-center">
                                        <h2 class="text-3xl  text-center font-bold">
                                            {{ __('welcome.get-on-board-now') }}
                                        </h2>
                                        <a href="{{ route('register') }}">
                                            <button class="btn text-lg mt-16 px-12 btn-primary normal-case">
                                                {{ __('welcome.get-on-board') }}
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </main>
                            <div>
                                <div class="bg-slate-50 flex justify-center">
                                    <footer class="footer bg-neutral text-neutral-content py-10 px-14 md:px-28">
                                        <aside>
                                            <img width="50" height="50" src="{{ asset('images/icons/logo.svg') }}"
                                                 alt="Logo"/>
                                            <p>
                                                #Träwelling
                                            </p>
                                            <p>
                                                {{ __('welcome.footer.made-by') }}
                                            </p>
                                            <p>
                                            </p>
                                        </aside>

                                        <nav>
                                            <h6 class="footer-title">{{ __('welcome.footer.social') }}</h6>
                                            <div class="grid grid-flow-col gap-4">
                                                <a href="https://chaos.social/@Traewelling" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                         class="inline-block w-8 h-8 stroke-current"
                                                    >
                                                        <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path fill="currentColor"
                                                              d="M433 179.1c0-97.2-63.7-125.7-63.7-125.7-62.5-28.7-228.6-28.4-290.5 0 0 0-63.7 28.5-63.7 125.7 0 115.7-6.6 259.4 105.6 289.1 40.5 10.7 75.3 13 103.3 11.4 50.8-2.8 79.3-18.1 79.3-18.1l-1.7-36.9s-36.3 11.4-77.1 10.1c-40.4-1.4-83-4.4-89.6-54a102.5 102.5 0 0 1 -.9-13.9c85.6 20.9 158.7 9.1 178.8 6.7 56.1-6.7 105-41.3 111.2-72.9 9.8-49.8 9-121.5 9-121.5zm-75.1 125.2h-46.6v-114.2c0-49.7-64-51.6-64 6.9v62.5h-46.3V197c0-58.5-64-56.6-64-6.9v114.2H90.2c0-122.1-5.2-147.9 18.4-175 25.9-28.9 79.8-30.8 103.8 6.1l11.6 19.5 11.6-19.5c24.1-37.1 78.1-34.8 103.8-6.1 23.7 27.3 18.4 53 18.4 175z"/>
                                                    </svg>
                                                </a>
                                                <a href="https://x.com/Traewelling" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                         class="inline-block w-8 h-8 stroke-current"
                                                    >
                                                        <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path fill="currentColor"
                                                              d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
                                                    </svg>
                                                </a>
                                                <a href="https://github.com/traewelling/traewelling" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"
                                                         class="inline-block w-8 h-8 stroke-current"
                                                    >
                                                        <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path fill="currentColor"
                                                              d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3 .3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5 .3-6.2 2.3zm44.2-1.7c-2.9 .7-4.9 2.6-4.6 4.9 .3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3 .7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3 .3 2.9 2.3 3.9 1.6 1 3.6 .7 4.3-.7 .7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3 .7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3 .7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </nav>

                                        <nav>
                                            <h6 class="footer-title">{{ __('welcome.footer.links') }}</h6>
                                            <div class="grid grid-flow-row gap-4">
                                                <a href="https://help.traewelling.de/faq/" target="_blank">
                                                    {{ __('menu.about') }}
                                                </a>
                                                <a href="https://blog.traewelling.de" target="blog">
                                                    {{ __('menu.blog') }}
                                                </a>
                                                <a href="{{ route('legal.privacy') }}">
                                                    {{ __('menu.privacy') }}
                                                </a>
                                                <a href="{{ route('legal.notice') }}">
                                                    {{ __('menu.legal-notice') }}
                                                </a>
                                            </div>
                                        </nav>
                                    </footer>
                                </div>
                                <div class="p-4 flex justify-center bg-secondary">
                                    <div class="max-w-5xl text-slate-50">
                                        <p>
                                            &copy; {{date('Y')}} Tr&auml;welling
                                            &#45;
                                            {{ __('welcome.footer.version') }}
                                            <a href="{{route('changelog')}}">{{ VersionController::getVersion() }}</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="drawer-side">
                    <label for="my-drawer-3" class="drawer-overlay"></label>
                    <ul class="menu p-4 w-80 h-full bg-base-100">
                        @include('welcome.partials.nav-links')
                        <a href="{{ route('login') }}">
                            <button class="btn btn-sm text-xs normal-case md:btn-ghost mt-4 btn-block">
                                {{ __('user.login') }}
                            </button>
                        </a>
                        <a href="{{ route('register') }}">
                            <button
                                class="btn md:mt-0 mt-4 btn-block btn-sm text-xs btn-outline normal-case hover:text-white hover:btn-primary">
                                {{ __('welcome.get-on-board') }}
                            </button>
                        </a>
                    </ul>
                </div>
            </div>
        </div>

        @include('welcome.partials.mastodon-modal')
    </body>
</html>
