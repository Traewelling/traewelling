@extends('layouts.app')

@section('title')Dashboard @endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if($userSearchResponse->count() == 0)
                    <div class="col-md-8 offset-md-2">
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

                            <div class="col pl-0">
                                <a href="{{ route('account.show', ['username' => $user->username]) }}">
                                    <h4>{{ $user->name }}@if($user->private_profile)<i
                                                class="fas fa-user-lock"></i>@endif <small
                                                class="text-muted">{{ '@'.$user->username }}</small></h4>
                                </a>
                                <h6>
                                    <small>
                                        <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{ $user->train_distance }}</span><span
                                                class="small font-weight-lighter">km</span>
                                        <span class="font-weight-bold pl-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}</span>
                                        <span class="font-weight-bold pl-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;{{ $user->points }}</span><span
                                                class="small font-weight-lighter">{{__('profile.points-abbr')}}</span>
                                    </small>
                                </h6>
                                @if($user !== Auth::user() && !$user->private_profile)
                                    {{-- This needs to be refined with the "request follow"-feature --}}
                                    @if(Auth::user()->follows->where('id', $user->id)->first() === null)
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
                                @elseif($user == Auth::user())
                                    <a href="{{ route('settings') }}"
                                       class="btn btn-sm btn-primary">{{ __('profile.settings') }}</a>
                                @endif
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
