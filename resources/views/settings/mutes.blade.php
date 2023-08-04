@extends('layouts.settings')

@section('title')
    {{ __('menu.settings.myFollower') }}
@endsection

@section('content')
    <div class="card mt-3">
        <div class="card-header">{{ __('user.muted.heading2') }}</div>
        <div class="card-body table-responsive px-0">
            <table class="table table-striped table-hover">
                <tbody>
                    @foreach(auth()->user()->mutedUsers as $user)
                        <tr style="vertical-align: middle">
                            <td>
                                <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                    <a href="{{ route('profile', ['username' => $user->username]) }}">
                                        <img src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($user) }}"
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
                                      action="{{route('user.unmute')}}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{$user->id}}"/>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="far fa-eye"></i> {{ __('user.unmute-tooltip') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
