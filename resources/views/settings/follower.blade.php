@extends('layouts.settings')

@section('title')
    {{ __('menu.settings.myFollower') }}
@endsection

@section('content')
    @if($requests->count() > 0)
        <div class="card mt-3">
            <div class="card-header">{{ __('menu.settings.follower-requests') }}</div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <tbody>
                        @foreach($requests as $request)
                            <tr style="vertical-align: middle">
                                <td>
                                    <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                        <a href="{{ route('profile', ['username' => $request->user->username]) }}">
                                            <img src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($request->user) }}"
                                                 style="height: 3em;" alt="{{ $request->user->username }}">
                                            />
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{route('profile', ['username' => $request->user->username])}}">
                                        {{$request->user->name}}
                                        @if($request->user->name != $request->user->username)
                                            <br/>
                                            <small>{{'@' . $request->user->username}}</small>
                                        @endif
                                    </a>
                                </td>
                                <td class="pe-0">
                                    <form method="POST" action="{{route('settings.follower.reject')}}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{$request->user->id}}"/>
                                        <button type="submit" class="btn btn-danger"
                                                data-mdb-toggle="tooltip"
                                                data-mdb-placement="top"
                                                title="{{__('settings.request.delete')}}">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="ps-0">
                                    <form method="POST" action="{{route('settings.follower.approve')}}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{$request->user->id}}"/>
                                        <button type="submit" class="btn btn-success"
                                                data-mdb-toggle="tooltip"
                                                data-mdb-placement="top"
                                                title="{{__('settings.request.accept')}}">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{$requests->links()}}
            </div>
        </div>
    @endif
    <div class="card mt-3">
        <div class="card-header">{{ __('menu.settings.myFollower') }}</div>

        <div class="card-body table-responsive">

            @if($followers->count() == 0)
                <b class="text-danger">
                    <i class="fas fa-users-slash"></i>
                    {{__('settings.follower.no-follower')}}
                </b>
            @else

                <table class="table table-striped table-hover">
                    <tbody>
                        @foreach($followers as $follower)
                            <tr style="vertical-align: middle">
                                <td>
                                    <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                        <a href="{{ route('profile', ['username' => $follower->user->username]) }}">
                                            <img
                                                    src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($follower->user) }}"
                                                    style="height: 4em;"
                                            />
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{route('profile', ['username' => $follower->user->username])}}">
                                        {{$follower->user->name}}
                                        @if($follower->user->name != $follower->user->username)
                                            <br/>
                                            <small>{{'@' . $follower->user->username}}</small>
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <form method="POST" action="{{route('settings.follower.remove')}}">
                                        @csrf
                                        <input type="hidden" name="user_id"
                                               value="{{$follower->user_id}}"/>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-user-minus"></i>
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
@endsection
