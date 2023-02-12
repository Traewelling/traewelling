@extends('admin.layout')

@section('title', 'User: ' . $user->username)

@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card">
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
                                {{ $user->email }}

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
                            <th>Twitter</th>
                            <td>{{ $user->twitterUrl }}</td>
                        </tr>
                        <tr>
                            <th>Mastodon</th>
                            <td>{{ $user->mastodonUrl }}</td>
                        </tr>
                        <tr>
                            <th>Last login</th>
                            <td>{{ $user->last_login }}</td>
                        </tr>
                        <tr>
                            <th>Created at</th>
                            <td>{{ $user->created_at }}</td>
                        </tr>
                        <tr>
                            <th>Privacy ack at</th>
                            <td>{{ $user->privacy_ack_at }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
