@extends('layouts.app')

@section('title')
    {{ __('menu.login') }}
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card">
                    <div class="card-header">{{ __('user.login') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-group row mb-3">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-right">{{ __('user.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror" name="email"
                                           value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="password"
                                       class="col-md-4 col-form-label text-md-right">{{ __('user.password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror" name="password"
                                           required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember"
                                               id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('user.remember-me') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 col-lg-7 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('user.login') }}
                                    </button>

                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('user.forgot-password') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-7">
                                <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <a href="{{ url('/auth/redirect/twitter') }}"
                                               class="btn btn-md btn-primary mt-2"><i class="fab fa-twitter"></i>
                                                Twitter</a>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div class="md-form input-group mt-2">
                                                <input type="text" name="domain" class="form-control"
                                                       placeholder="{{__('user.mastodon-instance-url')}}"
                                                       aria-describedby="button-addon4">
                                                <button class="btn btn-md btn-primary m-0 px-3" type="submit"><i
                                                            class="fab fa-mastodon"></i> Mastodon
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
