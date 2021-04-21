@extends('layouts.app')

@section('title'){{ $user->name }}@endsection

@section('content')
    <div class="px-4 py-5 mt-n4"
         style="background-image: url({{url('/images/covers/profile-background.png')}});background-position: center;background-color: #c5232c">
        <div class="container">
            <img src="{{ route('account.showProfilePicture', ['username' => $user->username]) }}" height="15%"
                 width="15%" class="float-end img-thumbnail rounded-circle img-fluid">
            <div class="text-white px-4">
                <h2 class="card-title h1-responsive font-bold">
                    <strong>{{ $user->name }} @if($user->private_profile) <i class="fas fa-user-lock"></i>@endif
                    </strong> <br/>
                    <small class="font-weight-light">{{ '@'. $user->username }}</small>
                    @if($currentUser)
                        {{-- What the actual fuck are these stupid nested if-statements?! --}}
                        {{-- ToDo This needs to be refined with the "request follow"-feature --}}
                        @if($user->id !== $currentUser->id && Auth::check() && !$user->private_profile)
                            @if($currentUser->follows->where('id', $user->id)->first() === null)
                                <a href="#" class="btn btn-sm btn-primary follow" data-userid="{{ $user->id }}"
                                   data-following="no">{{__('profile.follow')}}</a>
                            @else
                                <a href="#" class="btn btn-sm btn-danger follow" data-userid="{{ $user->id }}"
                                   data-following="yes">{{__('profile.unfollow')}}</a>
                            @endif
                            <script>
                                window.translFollow = "{{__('profile.follow')}}";
                                window.translUnfollow = "{{__('profile.unfollow')}}";
                            </script>
                        @elseif($user->id == $currentUser->id)
                            <a href="{{ route('settings') }}"
                               class="btn btn-sm btn-primary">{{ __('profile.settings') }}</a>
                        @endif
                    @endif
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
    @include('includes.message-block')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <header><h3>&nbsp;</h3></header>
            </div>
        </div>
        <div class="row justify-content-center">
            @if($user->private_profile && $user != $currentUser)
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <header><h3>{{__('profile.private-profile-text')}}</h3></header>
                    <h5 hidden>{{__('profile.private-profile-information-text', ["username" => $user->username])}}</h5>
                </div>
            @else
                <div class="col-md-8 col-lg-7">
                    <header><h3>{{__('profile.last-journeys-of')}} {{ $user->name }}:</h3></header>
                    @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
                </div>
        </div>
        <div class="row justify-content-center mt-5">
            {{ $statuses->links() }}
        </div>
        @endif
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div>
@endsection
