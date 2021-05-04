@extends('layouts.app')

@section('title') {{ __('menu.settings.myFollower') }} @endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if($requests->count() > 0)
                <div class="col-md-8 col-lg-5 mb-3">
                    <div class="card">
                        <div class="card-header">{{ __('menu.settings.follower-requests') }}</div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-hover">
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr style="vertical-align: middle">
                                            <td>
                                                <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                                    <a href="{{ route('account.show', ['username' => $request->user->username]) }}">
                                                        <img src="{{ route('account.showProfilePicture', ['username' => $request->user->username]) }}"
                                                             style="height: 3em;"
                                                        />
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{route('account.show', ['username' => $request->user->username])}}">
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
                </div>
            @endif
            <div class="col-md-8 col-lg-7">
                <div class="card">
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
                                                    <a href="{{ route('account.show', ['username' => $follower->username]) }}">
                                                        <img src="{{ route('account.showProfilePicture', ['username' => $follower->username]) }}"
                                                             style="height: 4em;"
                                                        />
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{route('account.show', ['username' => $follower->username])}}">
                                                    {{$follower->name}}
                                                    @if($follower->name != $follower->username)
                                                        <br/>
                                                        <small>{{'@' . $follower->username}}</small>
                                                    @endif
                                                </a>
                                            </td>
                                            <td>
                                                <form method="POST" action="{{route('settings.follower.remove')}}">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{$follower->id}}"/>
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
            </div>
        </div>
    </div>
@endsection
