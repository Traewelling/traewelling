@extends('layouts.app')

@section('title'){{ $user->name }}@endsection

@section('metadata')
    @if($user->prevent_index)
        <meta name="robots" content="noindex"/>
    @endif
@endsection

@section('content')
    <div class="px-4 py-5 mt-n4"
         style="background-image: url({{url('/images/covers/profile-background.png')}});background-position: center;background-color: #c5232c">
        <div class="container">
            <img alt="{{ __('settings.picture') }}" src="{{ route('account.showProfilePicture', ['username' => $user->username]) }}" height="20%"
                 width="20%" class="float-end img-thumbnail rounded-circle img-fluid"/>
            <div class="text-white px-4">
                <h2 class="card-title h1-responsive font-bold">
                    <strong>{{ $user->name }} @if($user->private_profile) <i class="fas fa-user-lock"></i>@endif
                    </strong> <br/>
                    <small class="font-weight-light">{{ '@'. $user->username }}</small>
                    @auth
                        @include('includes.follow-button')

                        @if(auth()->user()->mutedUsers->contains('id', $user->id))
                            <form style="display: inline;" method="POST" action="{{route('user.unmute')}}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{$user->id}}"/>
                                <button type="submit" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
                                        title="{{ __('user.unmute-tooltip') }}">
                                    <i class="far fa-eye"></i>
                                </button>
                            </form>
                        @else
                            <form style="display: inline;" method="POST" action="{{route('user.mute')}}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{$user->id}}"/>
                                <button type="submit" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
                                        title="{{ __('user.mute-tooltip') }}">
                                    <i class="far fa-eye-slash"></i>
                                </button>
                            </form>
                        @endif
                    @endauth
                </h2>
                <h2>
                    <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{ number($user->train_distance) }}</span><span
                            class="small font-weight-lighter">km</span>
                    <span class="font-weight-bold ps-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}</span>
                    <span class="font-weight-bold ps-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;{{ $user->points }}</span><span
                            class="small font-weight-lighter">{{__('profile.points-abbr')}}</span>
                    @if($twitterUrl)
                        <span class="font-weight-bold ps-sm-2">
                            <a href="{{ $twitterUrl }}" rel="me" class="text-white" target="_blank">
                                <i class="fab fa-twitter d-inline"></i>
                            </a>
                        </span>
                    @endif
                    @if($mastodonUrl)
                        <span class="font-weight-bold ps-sm-2">
                            <a href="{{ $mastodonUrl }}" rel="me" class="text-white" target="_blank">
                                <i class="fab fa-mastodon d-inline"></i>
                            </a>
                        </span>
                    @endif
                </h2>

            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <header><h3>&nbsp;</h3></header>
            </div>
        </div>

        <div class="row justify-content-center">
            @if(auth()->check() && auth()->user()->mutedUsers->contains('id', $user->id))
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <header><h3>{{__('user.muted.heading')}}</h3></header>
                    <h5>{{__('user.muted.text', ["username" => $user->username])}}</h5>

                    <form method="POST" action="{{route('user.unmute')}}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{$user->id}}"/>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="far fa-eye"></i> {{ __('user.unmute-tooltip') }}
                        </button>
                    </form>
                </div>
            @elseif($user->userInvisibleToMe)
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <header><h3>{{__('profile.private-profile-text')}}</h3></header>
                    <h5>{{__('profile.private-profile-information-text', ["username" => $user->username, "request" => __('profile.follow_req')])}}</h5>
                </div>
            @elseif($statuses->count() > 0)
                <div class="col-md-8 col-lg-7">
                    <header><h3>{{__('profile.last-journeys-of')}} {{ $user->name }}:</h3></header>
                    @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
                </div>

                <div class="mt-5">
                    {{ $statuses->links() }}
                </div>
            @else
                <div class="col-md-8 col-lg-7">
                    <h3 class="text-danger">
                        {{strtr(__('profile.no-statuses'), [':username' => $user->name])}}
                    </h3>
                </div>
            @endif
        </div>

        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div>
@endsection
