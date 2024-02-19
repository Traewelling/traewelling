@extends('layouts.app')

@section('title', $user->name)
@section('canonical', route('profile', ['username' => $user->username]))

@if($user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', __('description.profile', [
        'username' => $user->name,
        'kmAmount' => number($user->train_distance / 1000, 0),
        'hourAmount' => number($user->train_duration / 60, 0)
    ]))
@endif

@section('content')
    <div class="px-md-4 py-md-5 py-4 mt-n4 profile-banner">
        <div class="container">
            <img alt="{{ __('settings.picture') }}"
                 src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($user) }}"
                 class="float-end img-thumbnail rounded-circle img-fluid profile-picture"/>
            <div class="text-white px-md-4">
                <h1 class="card-title h1-responsive font-bold mb-0 profile-name">
                    <strong>{{ $user->name }} @if($user->private_profile)
                            <i class="fas fa-user-lock"></i>
                        @endif
                    </strong>
                </h1>
                <span class="d-flex flex-column flex-md-row justify-content-md-start align-items-md-center gap-md-2 gap-1 pt-1 pb-2 pb-md-0">
                    <small class="font-weight-light profile-tag">{{ '@'. $user->username }}</small>
                    @auth
                        <div class="d-flex flex-row justify-content-md-start align-items-md-center gap-2">
                        @include('includes.follow-button')
                        @if(auth()->user()->id != $user->id)
                            <x-mute-button :user="$user"/>
                            <x-block-button :user="$user"/>
                        @endif
                        </div>
                    @endauth
                </span>

                @if(!$user->isAuthUserBlocked && !$user->isBlockedByAuthUser && !$user->muted)
                    <span class="profile-stats">
                            <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{ number($user->train_distance / 1000) }}</span><span
                                class="small font-weight-lighter">km</span>
			    <span class="font-weight-bold ps-sm-2">
                                <i class="fa fa-leaf d-inline"></i>&nbsp; {{ number_format($user->train_distance * 15 / 100 , 0)  }}t
                            </span>
                            <span class="font-weight-bold ps-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}</span>
                            <span class="font-weight-bold ps-sm-2">
                                <i class="fa fa-dice-d20 d-inline"></i>&nbsp;{{ $user->points }}
                            </span>
                            <span class="small font-weight-lighter">
                                {{__('profile.points-abbr')}}
                            </span>
                        @if($user->mastodonUrl)
                            <span class="font-weight-bold ps-sm-2">
                                <a href="{{ $user->mastodonUrl }}" rel="me" class="text-white" target="_blank">
                                    <i class="fab fa-mastodon d-inline"></i>
                                </a>
                            </span>
                        @endif
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center mt-4">
            @if($user->muted)
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <header><h3>{{__('user.muted.heading')}}</h3></header>
                    <h5>{{__('user.muted.text', ["username" => $user->username])}}</h5>

                    <x-mute-button :user="$user" :showText="true"/>
                </div>
            @elseif($user->isAuthUserBlocked)
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <span class="fs-3">{{__('profile.youre-blocked-text')}}</span>
                    <br/>
                    <span class="fs-5">
                        {{__('profile.youre-blocked-information-text', ['username' => $user->username])}}
                    </span>
                </div>
            @elseif($user->isBlockedByAuthUser)
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <span class="fs-3">{{__('profile.youre-blocking-text', ['username' => $user->username])}}</span>
                    <br/>
                    <span class="fs-5">
                        {{__('profile.youre-blocking-information-text')}}
                    </span>
                </div>
            @elseif($user->private_profile && !$user->following && (!auth()->check() || $user->id !== auth()->id()))
                <div class="col-md-8 col-lg-7 text-center mb-5">
                    <span class="fs-3">{{__('profile.private-profile-text')}}</span>
                    <br/>
                    <span class="fs-5">
                        {{__('profile.private-profile-information-text', ['username' => $user->username, 'request' => __('profile.follow_req')])}}
                    </span>
                </div>
            @elseif(isset($statuses) && $statuses->count() > 0)
                <div class="col-md-8 col-lg-7">
                    <h1 class="fs-3">{{__('profile.last-journeys-of')}} {{ $user->name }}:</h1>
                    @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
                </div>

                <div class="mt-5">
                    {{ $statuses->onEachSide(1)->links() }}
                </div>
            @else
                <div class="col-md-8 col-lg-7">
                    <span class="text-danger fs-3">
                        @if($user->train_distance > 0)
                            {{__('profile.no-visible-statuses', ['username' => $user->name])}}
                        @else
                            {{__('profile.no-statuses', ['username' => $user->name])}}
                        @endif
                    </span>
                </div>
            @endif
        </div>

        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div>
@endsection
