@extends('layouts.app-tailwind-base')
@section('layout-content')
    <div id="__next">
        <div class="flex flex-col">
            <div id="welcome-navbar"></div>
            <div>
                <main>
                    <div class="hero pt-4 md:pt-12 pb-12 bg-primary bg-center bg-cover"
                         style="background-image: url('{{ \Illuminate\Support\Facades\Vite::asset('resources/images/covers/profile-background.png') }}')">
                        <div class="hero-content flex-col lg:flex-row-reverse w-full max-w-6xl gap-12">
                            <div class="lg:flex-1">
                                <h1 class="text-5xl text-slate-100 font-bold md:leading-none leading-tight mt-0">
                                    {{ __('welcome.header.title') }}
                                </h1>
                                <p class="py-2 text-xl text-slate-100 mt-4 pr-12">
                                    {{ __('welcome.header.track') }}
                                </p>
                                <p class="py-2 text-xl text-slate-100 mt-4 pr-12">
                                    {{ __('welcome.header.open-source') }}
                                </p>
                                <div id="welcome-links" class="mt-4"
                                     data-about-label="{{ __('menu.about') }}"
                                     data-active-label="{{ __('menu.active') }}"
                                     data-source-label="{{ __('footer.sourcecode') }}"></div>
                            </div>
                            <div id="welcome-login" class="w-full lg:w-2/5 rounded-box bg-base-200 p-6 max-w-md"></div>
                        </div>
                    </div>
                    @if($stats->hasData())
                        <div class="grid place-items-center bg-base-200 w-full">
                            <div class="max-w-6xl w-full py-10 px-4 content-center justify-center">
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

                    <div class="bg-base-200">
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
                        <div class="max-w-6xl py-12 px-4 content-center text-center justify-center">
                            <h2 class="text-3xl text-center font-bold text-base-content">
                                {{ __('welcome.get-on-board-now') }}
                            </h2>
                            <a href="{{ route('register') }}">
                                <button class="btn text-lg mt-8 px-12 btn-primary normal-case">
                                    {{ __('welcome.get-on-board') }}
                                </button>
                            </a>
                        </div>
                    </div>
                </main>
                <div id="welcome-footer"></div>
            </div>
        </div>
    </div>

    @vite('resources/tailwind-app/welcome.ts')
@endsection
