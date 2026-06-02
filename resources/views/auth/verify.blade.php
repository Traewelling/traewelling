@extends('layouts.minimal-tailwind')

@section('title', __('menu.register'))
@section('meta-robots', 'noindex')

@section('content')
    <div class="bg-base-200 flex min-h-screen items-center w-full">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            @if (session('resent'))
                <div class="alert alert-success mb-4" role="alert">
                    <span>
                                {{ __('user.fresh-link') }}
                    </span>
                </div>
            @endif
            <div class="bg-base-100 md:rounded-xl">
                <div class="px-10 py-24">
                    <h1 class="text-3xl font-bold self-center">{{ __('user.email-verify') }}</h1>
                    <div class="flex flex-col gap-4">

                        {{ __('user.please-check') }}
                        {{ __('user.not-received-before') }} <a class="link link-primary" href="{{ route('verification.resend') }}">{{ __('user.not-received-link') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div
@endsection
