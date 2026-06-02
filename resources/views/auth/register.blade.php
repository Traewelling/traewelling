@php use App\Http\Controllers\Backend\VersionController; @endphp
@extends('layouts.minimal-tailwind')

@section('title', __('menu.register'))
@section('meta-robots', 'noindex')

@section('content')
    <div class="bg-base-200 flex min-h-screen items-center w-full">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div class="bg-base-100 grid grid-cols-1 md:grid-cols-2 md:rounded-xl">
                <div class="">
                    <div class="hero bg-base-200 min-h-full md:rounded-l-xl"
                         style="background-image: url(&quot;/images/covers/register.jpg&quot;); background-size: cover; background-position: center center;">
                        <div class="hero-content py-12 text-shadow-md">
                            <div class="max-w-md">
                                <h1 class="text-center text-3xl font-bold text-white mix-blend-difference">
                                    <a href="/">
                                    <img src="/images/icons/logo.svg" class="h-12 w-12 inline-block fill-current"
                                         alt="Träwelling Logo" style="stroke: #c72730;"/>
                                    &nbsp; {{ config('app.name')  }}
                                    </a>
                                </h1>
                                <h5 class="text-center text-gray-400">{{ VersionController::getVersion() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-10 py-12">
                    @if(config('app.registration.enabled'))
                        @include('welcome.partials.register')
                    @else
                        <div class="alert alert-info text-center" role="alert">
                            {{ __('user.registration-disabled') }}
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('welcome.partials.mastodon-modal')
@endsection
