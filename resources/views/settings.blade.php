@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Settings') }}</div>

                <div class="card-body">
                    <form enctype="multipart/form-data" method="POST" action="{{ route('settings') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Profile Picture') }}</label>

                            <div class="col-md-6">

                                <div class="custom-file">
                                    <input type="file" name="avatar" class="custom-file-input" id="customFile">
                                    <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
                                </div>

                                @error('username')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

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
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Password -->
            <div class="card mt-3">
                <div class="card-header">{{ __('Password') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.change') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Current Password') }}</label>

                            <div class="col-md-6">
                                <input id="currentpassword" type="password" class="form-control @error('currentpassword') is-invalid @enderror" name="currentpassword" required>

                                @error('currentpassword')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('New Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required>

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
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            &nbsp;<!-- Login Providers -->
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
                                <td><a href="#" data-provider="twitter" class="btn btn-sm btn-outline-danger disconnect">{{ __('Disconnect') }}</a></td>
                            @else
                                <td>{{ __('Not Connected') }}</td>
                                <td><a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a></td>
                            @endif
                        </tr>
                        <tr>
                            <td>Mastodon</td>
                            @if ($user->socialProfile->mastodon_id != null)
                                <td>{{ __('Connected') }}</td>
                                <td><a href="#" data-provider="mastodon" class="btn btn-sm btn-outline-danger disconnect">{{ __('Disconnect') }}</a></td>
                            @else

                                <td>{{ __('Not Connected') }}</td>
                                <td>
                                    <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                    <div class="input-group">
                                        <input type="text" name="domain" placeholder="Instance URL" aria-describedby="button-addon4" class="form-control">
                                        <div id="button-addon4" class="input-group-append">
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fab fa-mastodon"></i> {{ __('Connect') }}</button>
                                        </div>
                                    </div>
                                    </form>
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td>Github</td>
                            @if ($user->socialProfile->github_id != null)
                                <td>{{ __('Connected') }}</td>
                                <td><a href="#" data-provider="github" class="btn btn-sm btn-outline-danger disconnect">{{ __('Disconnect') }}</a></td>
                            @else
                                <td>{{ __('Not Connected') }}</td>
                                <td><a href="{{ url('/auth/redirect/github') }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a></td>
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

            <!-- Sessions -->
            <div class="card mt-3">
                <div class="card-header">{{ __('Sessions') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ __('Device') }}</th>
                            <th></th>
                            <th>{{ __('IP') }}</th>
                            <th>{{ __('Last Activity') }}</th>
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
                    <a href="{{ route('delsession') }}" class="btn btn-block btn-outline-danger" role="button">{{ __('Delete all sessions') }}</a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Account löschen</div>
                <div class="card-body">
                    <a class="btn btn-block btn-outline-danger" role="button" data-toggle="modal" data-target="#deleteUserModal">{{ __('Delete my account') }}</a>

                    
                    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Account löschen</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Bei Bestätigung werden alle mit dem Account verknüpften Daten auf {{env('APP_NAME')}} unwiderruflich gelöscht.<br /> Tweets und Toots, die über {{env('APP_NAME')}} gesendet wurden, werden nicht gelöscht. 
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-grey" data-dismiss="modal">Zurück</button>
                                    <a href="{{ route('account.destroy') }}" role="button" class="btn btn-red">Wirklich löschen</a>
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
