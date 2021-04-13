@extends('layouts.app')

@section('title') {{ __('menu.settings-myFollower') }} @endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('menu.settings-myFollower') }}</div>

                    <div class="card-body table-responsive ">

                        @if($followers->count() == 0)
                            <b class="text-danger">
                                <i class="fas fa-users-slash"></i>
                                {{__('settings.follower.no-follower')}}
                            </b>
                        @else

                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th colspan="2">Follower</th>
                                    <th>{{__('settings.follower.following-since')}}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($followers as $follower)
                                    <tr>
                                        <td>
                                            <div class="image-box pr-0 d-lg-flex" style="width: 4em; height: 4em;">
                                                <a href="{{ route('account.show', ['username' => $follower->username]) }}">
                                                    <img src="{{ route('account.showProfilePicture', ['username' => $follower->username]) }}"
                                                         style="height: 3em;"
                                                    />
                                                </a>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle">
                                            <a href="{{route('account.show', ['username' => $follower->username])}}">
                                                {{$follower->name}}
                                                @if($follower->name != $follower->username)
                                                    <br/>
                                                    <small>{{'@' . $follower->username}}</small>
                                                @endif
                                            </a>
                                        </td>
                                        <td style="vertical-align: middle">{{$follower->created_at->diffForHumans()}}</td>
                                        <td>
                                            <form method="POST" action="{{route('settings.follower.remove')}}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{$follower->id}}"/>
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-users-slash"></i>
                                                    {{__('settings.follower.delete')}}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{$followers->links()}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
