@extends('layouts.app-tailwind-base')
@section('layout-content')
    <div id="__next">
        <div class="drawer"><input id="my-drawer-3" type="checkbox" class="drawer-toggle">
            <div class="drawer-content flex flex-col">
                <div class="flex flex-col">
                    <div class="w-full flex justify-center shadow-lg">
                        <div class="navbar py-2 bg-base-100 max-w-6xl">
                            <div class="navbar-start">
                                <div class="flex-none lg:hidden">
                                    <label for="my-drawer-3" class="btn btn-square btn-ghost"
                                           aria-label="{{ __('menu.toggle-navigation') }}">
                                        <span id="welcome-nav-toggle" class="inline-block size-5"></span>
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
                                <ul class="menu menu-horizontal">
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
                    <div>
                        <main>
                            <div class="hero pt-4 md:pt-12 pb-12 bg-gradient-to-t from-blue-500 to-purple-700">
                                <div class="hero-content flex-col lg:flex-row-reverse w-full lg:ps-28">
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
                            @if($stats->hasData())
                                <div class="grid place-items-center bg-base-200 w-full">
                                    <div class="max-w-6xl w-full py-24 px-4 content-center justify-center">
                                        <div id="welcome-stats"
                                             data-distance="{{ trans_choice('welcome.stats.million', $stats->distanceInMillionKilometers()) }}"
                                             data-distance-label="{{ __('welcome.stats.distance') }}"
                                             data-users="{{ $stats->userCount }}"
                                             data-users-label="{{ __('welcome.stats.registered') }}"
                                             data-duration="{{ $stats->durationInYears() }}"
                                             data-duration-label="{{ __('welcome.stats.duration') }}"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="my-10 bg-base-200">
                                <div class="mx-auto w-full max-w-6xl">
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row xl:gap-10">
                                        <div id="welcome-status-card" class="w-full"
                                             data-label="{{ __('welcome.hero.status.preview-label') }}"
                                             data-body="{{ __('welcome.hero.status.preview-body') }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">{{ __('welcome.hero.status.title') }}</h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.status.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row-reverse xl:gap-10">
                                        <div id="welcome-tags" class="w-full"
                                             data-label="{{ __('welcome.hero.tags.preview-label') }}"
                                             data-tags="{{ json_encode($showcaseTags) }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">{{ __('welcome.hero.tags.title') }}</h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.tags.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row xl:gap-10">
                                        <div id="welcome-stats-charts" class="w-full"
                                             data-label="{{ __('welcome.hero.stats.preview-label') }}"
                                             data-categories-label="{{ __('stats.categories') }}"
                                             data-operators-label="{{ __('stats.companies') }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">{{ __('welcome.hero.stats.title') }}</h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.stats.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row-reverse xl:gap-10">
                                        <div id="welcome-visibility" class="w-full"
                                             data-label="{{ __('welcome.hero.privacy.preview-label') }}"
                                             data-levels="{{ json_encode($visibilityLevels) }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">{{ __('welcome.hero.privacy.title') }}</h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.privacy.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row xl:gap-10">
                                        <div id="welcome-export" class="w-full"
                                             data-label="{{ __('welcome.hero.export.preview-label') }}"
                                             data-gdpr-label="{{ __('welcome.hero.export.gdpr') }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">{{ __('welcome.hero.export.title') }}</h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.export.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row-reverse xl:gap-10">
                                        <div id="welcome-api" class="w-full"
                                             data-label="{{ __('welcome.hero.api.preview-label') }}"
                                             data-docs-url="{{ route('l5-swagger.default.api') }}"
                                             data-docs-label="{{ __('welcome.hero.api.link') }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">{{ __('welcome.hero.api.title') }}</h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.api.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row xl:gap-10">
                                        <div id="welcome-community" class="w-full"
                                             data-label="{{ __('welcome.hero.community.preview-label') }}"
                                             data-contributors-label="{{ __('welcome.hero.community.contributors') }}"
                                             data-association-label="{{ __('welcome.hero.community.association') }}"
                                             data-donations-label="{{ __('welcome.hero.community.donations') }}"
                                             data-association-url="https://traewelling.org"
                                             data-association-link-label="{{ __('welcome.hero.community.link') }}"
                                             data-support-url="https://traewelling.org/support-us"
                                             data-support-label="{{ __('welcome.hero.community.support') }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">
                                                {{ __('welcome.hero.community.title') }}
                                            </h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.community.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero min-h-full rounded-lg">
                                    <div class="hero-content flex-col lg:flex-row-reverse xl:gap-10">
                                        <div id="welcome-self-hosting" class="w-full"
                                             data-label="{{ __('welcome.hero.self-hosting.preview-label') }}"
                                             data-repo-url="https://github.com/traewelling/traewelling"
                                             data-repo-label="{{ __('welcome.hero.self-hosting.link') }}"
                                             data-license-label="{{ __('welcome.hero.self-hosting.license') }}"></div>
                                        <div class="w-full">
                                            <h2 class="text-3xl font-bold">
                                                {{ __('welcome.hero.self-hosting.title') }}
                                            </h2>
                                            <p class="py-6">
                                                {{ __('welcome.hero.self-hosting.description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="grid place-items-center bg-base-200 w-full border-b-2 border-base-300">
                                <div class="max-w-6xl py-24 px-4 content-center text-center justify-center">
                                    <h2 class="text-3xl text-center font-bold text-base-content">
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
                            <div class="bg-base-200 flex justify-center">
                                <footer
                                    class="footer md:footer-horizontal bg-neutral text-neutral-content py-10 px-14 md:px-28">
                                    <aside>
                                        <img width="50" height="50" src="{{ asset('images/icons/logo.svg') }}"
                                             alt="{{ config('app.name', 'Träwelling') }}"/>
                                        <p>
                                            #Träwelling
                                        </p>
                                        <p>
                                            {{ __('welcome.footer.made-by') }}
                                        </p>
                                    </aside>

                                    <nav>
                                        <h6 class="footer-title">{{ __('welcome.footer.social') }}</h6>
                                        <div id="welcome-social-links"></div>
                                    </nav>

                                    <nav>
                                        <h6 class="footer-title">{{ __('welcome.footer.links') }}</h6>
                                        <div class="grid grid-flow-row gap-4">
                                            <a href="https://help.traewelling.de/features/" target="_blank"
                                               rel="noopener noreferrer">
                                                {{ __('menu.about') }}
                                            </a>
                                            <a href="https://blog.traewelling.de" target="_blank"
                                               rel="noopener noreferrer">
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
                                        &copy; {{ date('Y') }} Tr&auml;welling
                                        &#45;
                                        {{ __('welcome.footer.version') }}
                                        <a class="link" href="{{ route('changelog') }}">{{ $version }}</a>
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

    @vite('resources/tailwind-app/welcome.ts')
@endsection
