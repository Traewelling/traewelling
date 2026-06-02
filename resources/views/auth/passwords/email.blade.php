@php use App\Http\Controllers\Backend\VersionController; @endphp
@extends('layouts.minimal-tailwind')

@section('meta-robots', 'noindex')

@section('content')
<div class="bg-base-200 flex min-h-screen items-center w-full">
    <div class="card mx-auto w-full max-w-5xl shadow-xl">
        @if (session('status'))
            <div class="alert alert-success mb-4" role="alert">
                <span>{{ session('status') }}</span>
            </div>
        @endif
        <div class="bg-base-100 md:rounded-xl">
            <div class="px-10 py-24">
                <h1 class="text-3xl font-bold self-center">{{ __('user.header-reset-pw') }}</h1>
                <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
                    @csrf

                    <fieldset class="fieldset">
                        <div class="label">
                            {{ __('user.email') }}
                        </div>

                        <input id="email" type="email" class="input w-full @error('email') input-error @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                        @error('email')
                        <p class="validator-hint visible text-error" role="alert">
                            <strong>{{ $message }}</strong>
                        </p>
                        @enderror
                    </fieldset>

                    <button type="submit" class="btn btn-primary">
                        {{ __('user.reset-pw-link') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
