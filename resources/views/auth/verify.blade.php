@extends('layouts.app')

@section('title', __('menu.register'))
@section('meta-robots', 'noindex')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card">
                    <div class="card-header">{{ __('user.email-verify') }}</div>

                    <div class="card-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('user.fresh-link') }}
                            </div>
                        @endif

                        {{ __('user.please-check') }}
                        {{ __('user.not-received-before') }}, <a
                                href="{{ route('verification.resend') }}">{{ __('user.not-received-link') }}</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
