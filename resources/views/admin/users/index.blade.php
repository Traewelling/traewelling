@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-2">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="inputUsername" name="query"
                                       value="{{$query}}"
                                       placeholder="Username / Displayname / SupportCode"/>
                                <label for="inputUsername" class="form-label">Username / Displayname /
                                    SupportCode</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="inputId" name="userId" value="{{$userId}}"
                                       placeholder="UserID"/>
                                <label for="inputId" class="form-label">UserID</label>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Suchen</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" aria-labelledby="pageTitle">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Username</th>
                                    <th>Displayname</th>
                                    <th>Registrierung</th>
                                    <th>DSGVO-Accept</th>
                                    <th>Mail</th>
                                    <th class="text-center">Twitter</th>
                                    <th class="text-center">Mastodon</th>
                                    <th class="text-center">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($users as $user)
                                        <td class="text-center">
                                            <code>{{ $user->id }}</code>
                                        </td>
                                        <td>
                                            <a href="{{ route('profile', ['username' => $user->username]) }}"
                                               target="_blank">
                                                {{ '@'.$user->username }}
                                            </a>
                                        </td>
                                        <td>{{ $user->name }}</td>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 justify-content-center mb-5">
            {{ $users->withQueryString()->links() }}
        </div>

    </div>
@endsection
