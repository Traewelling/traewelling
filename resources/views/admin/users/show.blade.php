@php use Spatie\Permission\Models\Role; @endphp
@extends('admin.layout')

@section('title', 'User: ' . $user->username)

@section('actions')
    <a class="btn float-end btn-secondary" href="{{ route('profile', ['username' => $user->username]) }}">
        <i class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
        <span class="d-none d-md-inline">Frontend</span>
    </a>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <th>Mail</th>
                            <td>
                                {{ $user->email }} <a href="#mailCollapse" data-bs-toggle="collapse">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
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
                            </td>
                        </tr>
                        <tr class="collapse" id="mailCollapse">
                            <th></th>
                            <td>
                                <form method="post" action="{{ route('admin.users.update-mail') }}">
                                    <input type="hidden" name="id" value="{{ $user->id }}"/>
                                    <input type="text" name="email" class="form-control" placeholder="New email"/>
                                    <input type="submit" class="btn btn-primary" value="Change email"/>
                                    @csrf
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <th>Password</th>
                            <td>
                                @isset($user->password)
                                    <small class="text-success">
                                        <i class="fa-solid fa-check"></i>
                                        Set
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="fa-solid fa-times"></i>
                                        Not set
                                    </small>
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <th>Mastodon</th>
                            <td>
                                @if($user->mastodonUrl)
                                    <a href="{{ $user->mastodonUrl }}" target="_blank">
                                        {{ $user->mastodonUrl }} <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last login</th>
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
                        </tr>
                        <tr>
                            <th>Created at</th>
                            <td>{{ $user->created_at }}</td>
                        </tr>
                        <tr>
                            <th>Privacy ack at</th>
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
                        </tr>
                    </table>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">Roles</h2>
                    <form method="post" action="{{ route('admin.users.update-roles') }}">
                        <ul>
                            @foreach(Role::all() as $role)
                                <li>
                                    <input id="{{$role->name}}" type="checkbox" name="roles[{{ $role->name }}]"
                                           @if($role->name === 'admin') disabled @endif
                                           value="1" @if($user->hasRole($role->name)) checked @endif/>
                                    <label for="{{$role->name}}">{{ $role->name }}</label>
                                    <br/>
                                    @foreach($role->permissions->pluck('name') as $permission)
                                        <code>{{$permission}}</code> |
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="id" value="{{ $user->id }}"/>
                        <input type="submit" class="btn btn-primary" value="Update"/>
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row border-bottom">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-center">
                                    {{ round($user->train_distance / 1000) }} km
                                </h3>
                                <p class="text-center">
                                    total distance
                                </p>
                            </div>
                            <div class="col">
                                <h3 class="text-center">
                                    {!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}
                                </h3>
                                <p class="text-center">
                                    total duration
                                </p>
                            </div>
                            <div class="col">
                                <h3 class="text-center">
                                    {{ $user->points }}
                                </h3>
                                <p class="text-center">
                                    Points
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">Last statuses</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trip</th>
                                    <th>Origin / Destination</th>
                                    <th>Points</th>
                                    <th>Created at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->statuses()->orderByDesc('created_at')->limit(15)->get() as $status)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}">
                                                {{ $status->id }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{route('admin.trip.show', ['id' => $status->checkin->trip->id])}}">
                                                {{ $status->checkin->id }} ({{ $status->checkin->trip->source }})
                                            </a>
                                            <br/>
                                            <code>{{ $status->checkin->trip->linename }}</code>
                                        </td>
                                        <td>
                                            {{ $status->checkin->originStopover->station->name }}
                                            &rarr;
                                            {{ $status->checkin->destinationStopover->station->name }}
                                        </td>
                                        <td>{{ $status->checkin->points }}</td>
                                        <td>{{ $status->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

@endsection
