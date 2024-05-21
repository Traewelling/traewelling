@extends('admin.layout')

@section('title', 'Users')

@section('content')

    <form>
        <div class="input-group mb-3">
            <input type="text" name="query" class="form-control"
                   placeholder="Search by: UserID, Username or Displayname. Exact match: 'id:1234'"
                   value="{{ request('query') }}"
                   aria-describedby="button-search"/>
            <button class="btn btn-outline-secondary" type="button" id="button-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                Search
            </button>
        </div>
    </form>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" aria-labelledby="pageTitle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Last login</th>
                                    <th>Mail</th>
                                    <th>Privacy Agreement</th>
                                    <th class="text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($users as $user)
                                        <td>
                                            <a href="{{ route('admin.users.user', ['id' => $user->id]) }}">
                                                {{ '@'.$user->username }}
                                            </a>
                                            <code>({{ $user->id }})</code>
                                            <br/>
                                            {{ $user->name }}
                                        </td>
                                        <td>
                                            @isset($user->last_login)
                                                    {{ $user->last_login->diffForHumans() }}<br/>
                                                    <small>({{$user->last_login}})</small>
                                            @else
                                                <small class="text-danger">
                                                    <i class="fa-solid fa-times"></i>
                                                    Never logged in
                                                </small>
                                            @endisset
                                        </td>
                                        <td>
                                            @isset($user->email)
                                                {{ $user->email }}
                                                <br/>
                                                @isset($user->email_verified_at)
                                                    <small class="text-success">
                                                        <i class="fa-solid fa-check"></i>
                                                        Verified {{$user->email_verified_at->diffForHumans()}}
                                                    </small>
                                                @else
                                                    <small class="text-danger">
                                                        <i class="fa-solid fa-times"></i>
                                                        Not verified
                                                    </small>
                                                @endisset
                                            @endisset
                                        </td>
                                        <td>
                                            @isset($user->privacy_ack_at)
                                                <small class="text-success">
                                                    <i class="fa-solid fa-check"></i>
                                                    {{ $user->privacy_ack_at->diffForHumans() }}<br/>
                                                    ({{$user->privacy_ack_at}})
                                                </small>
                                            @else
                                                <small class="text-danger">
                                                    <i class="fa-solid fa-times"></i>
                                                    Not agreed to Privacy Agreement
                                                </small>
                                            @endisset
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                @isset($user->mastodonUrl)
                                                    <a href="{{ $user->mastodonUrl }}" target="mastodon{{$user->id}}"
                                                       class="btn btn-small btn-info">
                                                        <i class="fab fa-mastodon"></i>
                                                    </a>
                                                @else
                                                    <a href="javascript:void(0)"
                                                       class="btn btn-small btn-secondary disabled">
                                                        <i class="fab fa-mastodon"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.stationboard') }}?userQuery={{ $user->id }}"
                                                   class="btn btn-small btn-success" title="Neuen Checkin erstellen">
                                                    <i class="fas fa-plus-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 justify-content-center mb-5">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
@endsection
