@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Settings') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('settings') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Username') }}</label>

                            <div class="col-md-6">

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">@</span>
                                    </div>
                                    <input id="name" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ $user->username }}" required autofocus>
                                </div>

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Displayname') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $user->name }}" required autocomplete="name" required>

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            &nbsp;
            <div class="card">
                <div class="card-header">{{ __('Login-Services') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Service') }}</th>
                                <th></th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                    @if ($user->socialProfile != null)
                        <tr>
                            <td>Twitter</td>
                            @if ($user->socialProfile->twitter_id != null)
                                <td>{{ __('Connected') }}</td>
                                <td><a href="" class="btn btn-outline-danger">{{ __('Disconnect') }}</a></td>
                            @else
                                <td>{{ __('Not Connected') }}</td>
                                <td><a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-primary">{{ __('Connect') }}</a></td>
                            @endif
                        </tr>
                        <tr>
                            <td>Mastodon</td>
                            @if ($user->socialProfile->mastodon_id != null)
                                <td>{{ __('Connected') }}</td>
                                <td><a href="" class="btn btn-outline-danger">{{ __('Disconnect') }}</a></td>
                            @else
                                <td>{{ __('Not Connected') }}</td>
                                <td><a href="" class="btn btn-primary">{{ __('Connect') }}</a></td>
                            @endif
                        </tr>
                        <tr>
                            <td>Github</td>
                            @if ($user->socialProfile->github_id != null)
                                <td>{{ __('Connected') }}</td>
                                <td><a href="" class="btn btn-outline-danger">{{ __('Disconnect') }}</a></td>
                            @else
                                <td>{{ __('Not Connected') }}</td>
                                <td><a href="" class="btn btn-primary">{{ __('Connect') }}</a></td>
                            @endif
                        </tr>

                    @else
                        <tr>
                            <td>Twitter</td>
                            <td>{{ __('Not Connected') }}</td>
                            <td><a href="{{ url('/auth/redirect/twitter') }}">{{ __('Connect') }}</a></td>
                        </tr>
                        <tr>
                            <td>Mastodon</td>
                            <td>{{ __('Not Connected') }}</td>
                            <td><a href="">{{ __('Connect') }}</a></td>
                        </tr>
                    @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
