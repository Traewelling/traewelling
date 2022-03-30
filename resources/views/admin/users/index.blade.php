@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Users</h5>
                    <form class="row g-3">
                        <div class="col-md-6">
                            <label for="inputUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="inputUsername" name="query" value="{{$query}}">
                        </div>
                        <div class="col-md-6">
                            <label for="inputId" class="form-label">UserId</label>
                            <input type="text" class="form-control" id="inputId" name="userId" value="{{$userId}}">
                        </div>
                        <div class="col-md-12 justify-content-end text-end">
                            <button type="submit" class="btn btn-primary">Suchen</button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
    <div class="row mt-1">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <td class="text-center">#</td>
                            <td>Username</td>
                            <td>Displayname</td>
                            <td class="text-center">Supportcode</td>
                            <td>Registrierung</td>
                            <td>DSGVO-Accept</td>
                            <td>Mail</td>
                            <td class="text-center">Twitter</td>
                            <td class="text-center">Mastodon</td>
                            <td class="text-center">Aktionen</td>
                        </tr>
                    </thead>
                    <tr>
                        @foreach($users as $user)
                        <td class="text-center">
                            <code>{{ $user->id }}</code>
                        </td>
                        <td>
                            <a href="{{ route('profile', ['username' => $user->username]) }}" target="_blank">
                                {{ '@'.$user->username }}
                            </a>
                        </td>
                        <td>{{ $user->name }}</td>
                        <td class="text-center"><code>{{ $user->support_code }}</code></td>
                        <td>{{ $user->created_at }}</td>
                        <td>{{ $user->privacy_ack_at }}</td>
                        <td>{{ $user->email }}<br>{{ $user->email_verified_at }}</td>
                        <td class="text-center">
                            <a href="{{ $user->twitterUrl }}"
                               class="btn btn-small btn-info {{($user->twitterUrl) ? '' : 'disabled'}}">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="{{ $user->mastodonUrl }}"
                               class="btn btn-small btn-info {{($user->mastodonUrl) ? '' : 'disabled'}}">
                                <i class="fab fa-mastodon"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.stationboard') }}?userQuery={{ $user->id }}"
                               class="btn btn-small btn-success" title="Neuen Checkin erstellen">
                                <i class="fas fa-plus-circle"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-2 mb-5">
        {{ $users->withQueryString()->links() }}
    </div>
@endsection
