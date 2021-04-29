@extends('layouts.app')

@section('title')Dashboard @endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @if($userSearchResponse->count() == 0)
                    <div class="col-md-8 col-lg-7">
                        <div class="alert my-3 alert-danger" role="alert">
                            {{ __('user.no-user') }}
                        </div>
                    </div>
                @endif
                @foreach($userSearchResponse as $user)
                    <div class="card status mt-3">
                        <div class="card-body row">
                            <div class="col-2 image-box search-image-box d-lg-flex">
                                <a href="{{ route('account.show', ['username' => $user->username]) }}">
                                    <img src="{{ route('account.showProfilePicture', ['username' => $user->username]) }}"
                                         alt="profile picture">
                                </a>
                            </div>

                            <div class="col ps-0">
                                <a href="{{ route('account.show', ['username' => $user->username]) }}">
                                    <h4>{{ $user->name }}@if($user->private_profile)<i
                                                class="fas fa-user-lock"></i>@endif <small
                                                class="text-muted">{{ '@'.$user->username }}</small></h4>
                                </a>
                                <h6>
                                    <small>
                                        <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{ $user->train_distance }}</span><span
                                                class="small font-weight-lighter">km</span>
                                        <span class="font-weight-bold ps-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}</span>
                                        <span class="font-weight-bold ps-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;{{ $user->points }}</span><span
                                                class="small font-weight-lighter">{{__('profile.points-abbr')}}</span>
                                    </small>
                                </h6>
                                @include('includes.follow-button')
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            {{ $userSearchResponse->withQueryString()->links() }}
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div><!--- /container -->
@endsection
