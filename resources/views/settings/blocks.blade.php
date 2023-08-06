@extends('layouts.settings')
@section('title', __('user.blocked.heading2'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card mb-3">
                <div class="card-header">{{ __('user.blocked.heading2') }}</div>
                <div class="card-body table-responsive px-0">
                    <table class="table table-striped table-hover">
                        <tbody>
                            @foreach(auth()->user()->blockedUsers as $user)
                                <tr style="vertical-align: middle">
                                    <td>
                                        <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                            <a href="{{ route('profile', ['username' => $user->username]) }}">
                                                <img
                                                    src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($user) }}"
                                                    style="height: 3em;" alt="{{$user->username}}"
                                                />
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{route('profile', ['username' => $user->username])}}">
                                            {{$user->name}}
                                            @if($user->name != $user->username)
                                                <br/>
                                                <small>{{'@' . $user->username}}</small>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="pe-0">
                                        <form style="display: inline;" method="POST"
                                              action="{{route('user.unblock')}}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{$user->id}}"/>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="far fa-unlock"></i> {{ __('user.unblock-tooltip') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
