@extends('layouts.app')
@section('title')
    {{ __('menu.settings') }}
@endsection
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
                                <label for="name"
                                       class="col-md-4 col-form-label text-md-right">{{ __('settings.picture') }}</label>

                                <div class="col-md-6 text-center">
                                    <div class="image-box">
                                        <img
                                            src="{{ route('account.showProfilePicture', ['username' => $user->username]) }}"
                                            style="max-width: 96px" alt="{{__('settings.picture')}}" class="pb-2"
                                            id="theProfilePicture"/>
                                    </div>

                                    <a href="#" class="btn btn-primary" data-toggle="modal"
                                       data-target="#uploadAvatarModal">{{__('settings.upload-image')}}</a>

                                    @if($user->avatar != 'user.jpg')
                                        <a href="#" class="btn btn-outline-danger btn-sm"
                                           data-toggle="modal"
                                           data-target="#deleteProfilePictureModal"
                                        >{{ __('settings.delete-profile-picture-btn') }}</a>
                                    @endif

                                    @error('avatar')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="name"
                                       class="col-md-4 col-form-label text-md-right">{{ __('user.username') }}</label>

                                <div class="col-md-6">

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">@</span>
                                        </div>
                                        <input id="username" type="text"
                                               class="form-control @error('username') is-invalid @enderror"
                                               name="username" value="{{ $user->username }}" required autofocus>
                                    </div>

                                    @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="name"
                                       class="col-md-4 col-form-label text-md-right">{{ __('user.displayname') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text"
                                           class="form-control @error('name') is-invalid @enderror" name="name"
                                           value="{{ $user->name }}" required autocomplete="name" required>

                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-right">{{ __('user.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror" name="email"
                                           value="{{ $user->email }}" autocomplete="email">

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6 offset-md-4">
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input id="always_dbl" type="checkbox"
                                               class="custom-control-input @error('always_dbl') is-invalid @enderror"
                                               name="always_dbl" {{ $user->always_dbl ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                               for="always_dbl">{{ __('user.always-dbl') }}</label>
                                    </div>
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

                        <div class="modal fade" id="uploadAvatarModal" tabindex="-1" role="dialog"
                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="mb-0">{{__('settings.upload-image')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            <strong>{{__('settings.choose-file')}}: </strong>
                                            <input type="file" id="image">
                                        </p>

                                        <div class="d-none text-trwl text-center" id="upload-error"
                                             role="alert">{{ __('settings.something-wrong') }}</div>

                                        <div id="upload-demo" class="d-none"></div>
                                        <button class="btn btn-primary btn-block upload-image d-none"
                                                id="upload-button">{{__('settings.upload-image')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteProfilePictureModal" tabindex="-1" role="dialog"
                             aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="mb-0">{{__('settings.delete-profile-picture')}}:</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">x</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>{!! __('settings.delete-profile-picture-desc') !!}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-outline-danger" data-dismiss="modal"
                                                aria-label="{{ __('settings.delete-profile-picture-no') }}">
                                            {{ __('settings.delete-profile-picture-no') }}
                                        </button>
                                        <a href="{{ route('settings.delete-profile-picture') }}" class="btn btn-danger">
                                            {{ __('settings.delete-profile-picture-yes') }}
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
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
                                <label for="name"
                                       class="col-md-4 col-form-label text-md-right">{{ __('settings.current-password') }}</label>

                                <div class="col-md-6">
                                    <input id="currentpassword" type="password"
                                           class="form-control @error('currentpassword') is-invalid @enderror"
                                           name="currentpassword" autocomplete="current-password">

                                    @error('currentpassword')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-right">{{ __('settings.new-password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror" name="password"
                                           autocomplete="new-password" required>

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-right">{{ __('settings.confirm-password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           name="password_confirmation" autocomplete="new-password" required>

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
                        <table class="table table-responsive">
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
                                        <td><a href="#" data-provider="twitter"
                                               class="btn btn-sm btn-outline-danger disconnect">{{ __('settings.disconnect') }}</a>
                                        </td>
                                    @else
                                        <td>{{ __('settings.notconnected') }}</td>
                                        <td><a href="{{ url('/auth/redirect/twitter') }}"
                                               class="btn btn-sm btn-primary">{{ __('settings.connect') }}</a></td>
                                    @endif
                                </tr>
                                <tr>
                                    <td>Mastodon</td>
                                    @if ($user->socialProfile->mastodon_id != null)
                                        <td>{{ __('settings.connected') }}</td>
                                        <td><a href="#" data-provider="mastodon"
                                               class="btn btn-sm btn-outline-danger disconnect">{{ __('settings.disconnect') }}</a>
                                        </td>
                                    @else

                                        <td>{{ __('settings.notconnected') }}</td>
                                        <td>
                                            <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                                <div class="input-group mt-0">
                                                    <input type="text" name="domain" class="form-control"
                                                           placeholder="{{__('user.mastodon-instance-url')}}"
                                                           aria-describedby="button-addon4">
                                                    <div id="button-addon4" class="input-group-append">
                                                        <button class="btn btn-md btn-primary m-0 px-3" type="submit"><i
                                                                class="fab fa-mastodon"></i> {{ __('settings.connect') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                                <tr>

                            @else
                                <tr>
                                    <td>Twitter</td>
                                    <td>{{ __('settings.notconnected') }}</td>
                                    <td><a href="{{ url('/auth/redirect/twitter') }}"
                                           class="btn btn-sm btn-primary">{{ __('settings.connect') }}</a></td>
                                </tr>
                                <tr>
                                    <td>Mastodon</td>
                                    <td>{{ __('settings.notconnected') }}</td>
                                    <td>
                                        <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                            <div class="input-group mt-0">
                                                <input type="text" name="domain" class="form-control"
                                                       placeholder="{{__('user.mastodon-instance-url')}}"
                                                       aria-describedby="button-addon4">
                                                <div id="button-addon4" class="input-group-append">
                                                    <button class="btn btn-md btn-primary m-0 px-3" type="submit"><i
                                                            class="fab fa-mastodon"></i> {{ __('settings.connect') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Sessions -->
                <div class="card mt-3">
                    <div class="card-header">{{ __('settings.title-sessions') }}</div>

                    <div class="card-body">
                        <table class="table table-responsive">
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
                        <a href="{{ route('delsession') }}" class="btn btn-block btn-outline-danger mx-0"
                           role="button">{{ __('settings.deleteallsessions') }}</a>
                    </div>
                </div>

                <!-- API Tokens -->
                <div class="card mt-3">
                    <div class="card-header">{{ __('settings.title-tokens') }}</div>

                    <div class="card-body">
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th>{{ __('settings.client-name') }}</th>
                                <th>{{ __('settings.created') }}</th>
                                <th>{{ __('settings.updated') }}</th>
                                <th>{{ __('settings.expires') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            @foreach($tokens as $token)
                                <tr>
                                    <td>{{ $token['clientName'] }}</td>
                                    <td>{{ $token['created_at'] }}</td>
                                    <td>{{ $token['updated_at'] }}</td>
                                    <td>{{ $token['expires_at'] }}</td>
                                    <td>
                                        <a href="{{ route('deltoken', ['id' => $token['id']]) }}" alt="{{ __('settings.deletetokenfor') }}  {{ $token['clientName'] }}" class="btn btn-block btn-danger mx-0"
                                           role="button"><i class="fas fa-trash"></i></a></td>
                                </tr>
                            @endforeach

                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">{{ __('settings.delete-account') }}</div>
                    <div class="card-body">
                        <button class="btn btn-block btn-outline-danger mx-0" role="button" data-toggle="modal"
                                data-target="#deleteUserModal">{{ __('settings.delete-account') }}</button>


                        <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog"
                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"
                                            id="exampleModalLabel">{{__('settings.delete-account')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        {!! __('settings.delete-account-verify', ['appname' => config('app.name')])  !!}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-grey btn-sm"
                                                data-dismiss="modal">{{ __('settings.delete-account-btn-back') }}</button>
                                        <a href="{{ route('account.destroy') }}" role="button"
                                           class="btn btn-red btn-sm">{{ __('settings.delete-account-btn-confirm') }}</a>
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
