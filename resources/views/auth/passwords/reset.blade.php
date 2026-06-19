@extends('layouts.minimal-tailwind')

@section('meta-robots', 'noindex')

@section('content')
    <div class="bg-base-200 flex min-h-screen items-center w-full">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div class="bg-base-100 md:rounded-xl">
                <div class="px-10 py-24">
                    <h1 class="text-3xl font-bold self-center">{{ __('user.header-reset-pw') }}</h1>
                    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <fieldset class="fieldset">
                            <div class="label">
                                {{ __('user.email') }}
                            </div>

                            <input id="email" type="email" class="input w-full @error('email') input-error @enderror"
                                   name="email" required autocomplete="email" autofocus
                                   value="{{ $email ?? old('email') }}">

                            @error('email')
                            <p class="validator-hint visible text-error" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                            @enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <div class="label">
                                <span class="label-text">{{ __('user.password') }}</span>
                            </div>

                            <input type="password"
                                   class="input validator w-full @error('password') input-error @enderror" id="password"
                                   name="password" required
                                   value="{{ old('email') }}" autocomplete="new-password" minlength="8"/>
                            @error('password')
                            <p class="validator-hint text-error visible">
                                {{ $message }}
                            </p>
                            @enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <div class="label">
                                <span class="label-text">{{ __('settings.confirm-password') }}</span>
                            </div>

                            <input type="password" class="input w-full" id="password-confirm"
                                   name="password_confirmation" required
                                   value="{{ old('email') }}" autocomplete="new-password" minlength="8"/>
                        </fieldset>

                        <button type="submit" class="btn btn-primary">
                            {{ __('user.header-reset-pw') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
