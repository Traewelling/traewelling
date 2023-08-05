@extends('layouts.app')

@section('title', __('search-results'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @if($users->count() === 0)
                    <div class="alert alert-danger" role="alert">
                        {{ __('user.no-user') }}
                    </div>
                @endif
                @foreach($users as $user)
                    <div class="card status mt-3">
                        <div class="card-body row">
                            <div class="col-2 image-box search-image-box d-lg-flex">
                                <a href="{{ route('profile', ['username' => $user->username]) }}">
                                    <img
                                        src="{{\App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($user)}}"
                                        alt="Profile picture"
                                        loading="lazy"
                                        decoding="async"/>
                                </a>
                            </div>

                            <div class="col ps-0">
                                <span class="float-end mt-3">
                                    @include('includes.follow-button')
                                </span>
                                <a href="{{ route('profile', ['username' => $user->username]) }}"
                                   style="font-size: calc(1.26rem + .12vw)">
                                    {{ $user->name }}
                                    @if($user->private_profile)
                                        <i class="fas fa-user-lock"></i>
                                    @endif
                                    <small class="text-muted">{{ '@' . $user->username }}</small>
                                </a>
                                <br/>
                                <span style="font-size: 0.875em;">
                                    <span class="font-weight-bold">
                                        <i class="fa fa-route d-inline"></i>
                                        {{ number($user->train_distance / 1000) }}
                                    </span>
                                    <span class="small font-weight-lighter">km</span>
                                    <span class="font-weight-bold ps-sm-2">
                                        <i class="fa fa-stopwatch d-inline"></i>
                                        {!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}
                                    </span>
                                    <span class="font-weight-bold ps-sm-2">
                                        <i class="fa fa-dice-d20 d-inline"></i>
                                        {{ $user->points }}
                                    </span>
                                    <span class="small font-weight-lighter">{{__('profile.points-abbr')}}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
@endsection
