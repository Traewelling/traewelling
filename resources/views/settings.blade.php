@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('settings.title-profile') }}</div>

                <div class="card-body">
                    <form enctype="multipart/form-data" method="POST" action="{{ route('settings') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('settings.picture') }}</label>

                            <div class="col-md-6">
                                <img src="/uploads/avatars/{{ $user->avatar }}" style="max-width: 96px" alt="{{__('settings.picture')}}" class="pb-2" />

                                <div class="custom-file">
                                    <input type="file" name="avatar" class="custom-file-input" id="avatarUpload">
                                    <label class="custom-file-label" for="avatarUpload" id="avatarUpload-filename">{{ __('settings.choose-file') }}</label>
                                </div>

                                @error('avatar')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('user.username') }}</label>

                            <div class="col-md-6">

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">@</span>
                                    </div>
                                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ $user->username }}" required autofocus>
                                </div>

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('user.displayname') }}</label>

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
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('user.email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" autocomplete="email">

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
                                    {{ __('settings.btn-update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Password -->
            <div class="card mt-3">
                <div class="card-header">{{ __('settings.title-password') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.change') }}">
                        @csrf
                        <input type="hidden" name="username" autocomplete="username">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('settings.current-password') }}</label>

                            <div class="col-md-6">
                                <input id="currentpassword" type="password" class="form-control @error('currentpassword') is-invalid @enderror" name="currentpassword" autocomplete="current-password" required>

                                @error('currentpassword')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('settings.new-password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password" required>

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('settings.confirm-password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" autocomplete="new-password" required>

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('settings.btn-update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            &nbsp;<!-- Login Providers -->
            <div class="card">
                <div class="card-header">{{ __('settings.title-loginservices') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('settings.service') }}</th>
                                <th></th>
                                <th>{{ __('settings.action') }}</th>
                            </tr>
                        </thead>
                    @if ($user->socialProfile != null)
                        <tr>
                            <td>Twitter</td>
                            @if ($user->socialProfile->twitter_id != null)
                                <td>{{ __('settings.connected') }}</td>
                                <td><a href="#" data-provider="twitter" class="btn btn-sm btn-outline-danger disconnect">{{ __('settings.disconnect') }}</a></td>
                            @else
                                <td>{{ __('settings.notconnected') }}</td>
                                <td><a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-sm btn-primary">{{ __('settings.connect') }}</a></td>
                            @endif
                        </tr>
                        <tr>
                            <td>Mastodon</td>
                            @if ($user->socialProfile->mastodon_id != null)
                                <td>{{ __('settings.connected') }}</td>
                                <td><a href="#" data-provider="mastodon" class="btn btn-sm btn-outline-danger disconnect">{{ __('settings.disconnect') }}</a></td>
                            @else

                                <td>{{ __('settings.notconnected') }}</td>
                                <td>
                                    <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                    <div class="input-group">
                                        <input type="text" name="domain" placeholder="{{ __('user.mastodon-instance-url') }}" aria-describedby="button-addon4" class="form-control">
                                        <div id="button-addon4" class="input-group-append">
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fab fa-mastodon"></i> {{ __('settings.connect') }}</button>
                                        </div>
                                    </div>
                                    </form>
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td>Github</td>
                            @if ($user->socialProfile->github_id != null)
                                <td>{{ __('settings.connected') }}</td>
                                <td><a href="#" data-provider="github" class="btn btn-sm btn-outline-danger disconnect">{{ __('settings.disconnect') }}</a></td>
                            @else
                                <td>{{ __('settings.notconnected') }}</td>
                                <td><a href="{{ url('/auth/redirect/github') }}" class="btn btn-sm btn-primary">{{ __('settings.connect') }}</a></td>
                            @endif
                        </tr>

                    @else
                        <tr>
                            <td>Twitter</td>
                            <td>{{ __('settings.notconnected') }}</td>
                            <td><a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-sm btn-primary">{{ __('settings.connect') }}</a></td>
                        </tr>
                        <tr>
                            <td>Mastodon</td>
                            <td>{{ __('settings.notconnected') }}</td>
                            <td>
                                <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                <div class="input-group">
                                    <input type="text" name="domain" placeholder="{{ __('user.mastodon-instance-url') }}" aria-describedby="button-addon4" class="form-control">
                                    <div id="button-addon4" class="input-group-append">
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fab fa-mastodon"></i> {{ __('settings.connect') }}</button>
                                    </div>
                                </div>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>Github</td>
                            <td>{{ __('settings.notconnected') }}</td>
                            <td><a href="{{ url('/auth/redirect/github') }}" class="btn btn-sm btn-primary">{{ __('settings.connect') }}</a></td>
                        </tr>
                    @endif
                    </table>
                </div>
            </div>

            <!-- Sessions -->
            <div class="card mt-3">
                <div class="card-header">{{ __('settings.title-sessions') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ __('settings.device') }}</th>
                            <th>{{ __('settings.platform') }}</th>
                            <th>{{ __('settings.ip') }}</th>
                            <th>{{ __('settings.lastactivity') }}</th>
                        </tr>
                        </thead>
                        @foreach($sessions as $session)
                        <tr>
                            <td><i class="fas fa-{{ $session['device'] }}"></i></td>
                            <td>{{ $session['platform'] }}</td>
                            <td>{{ $session['ip'] }}</td>
                            <td>{{ date('Y-m-d H:i:s', $session['last']) }}</td>
                        </tr>
                        @endforeach

                    </table>
                    <a href="{{ route('delsession') }}" class="btn btn-block btn-outline-danger" role="button">{{ __('settings.deleteallsessions') }}</a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">{{ __('settings.delete-account') }}</div>
                <div class="card-body">
                    <a class="btn btn-block btn-outline-danger" role="button" data-toggle="modal" data-target="#deleteUserModal">{{ __('settings.delete-account') }}</a>


                    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">{{__('settings.delete-account')}}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    {!! __('settings.delete-account-verify', ['appname' => env('APP_NAME')])  !!}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-grey btn-sm" data-dismiss="modal">{{ __('settings.delete-account-btn-back') }}</button>
                            <a href="{{ route('account.destroy') }}" role="button" class="btn btn-red btn-sm">{{ __('settings.delete-account-btn-confirm') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
