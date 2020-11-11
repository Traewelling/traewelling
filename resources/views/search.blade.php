@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if($userSearchResponse->count() == 0)
                    <div class="col-md-8 offset-md-2">
                        <div class="alert my-3 alert-danger" role="alert">
                            Keinen Benutzer gefunden!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    </div>
                @endif
                @foreach($userSearchResponse as $user)
                <div class="card status mt-3" id="status-1" data-body="" data-date="Mittwoch, 07. Oktober 2020">

                        <div class="card-body row">
                            <div class="col-2 image-box d-none d-lg-flex">
                                <a href="{{ route('account.show', ['username' => $user->username]) }}">
                                    <img src="{{ route('account.showProfilePicture', ['username' => $user->username]) }}" alt="profile picture">
                                </a>
                            </div>

                            <div class="col pl-0">
                                <a href="{{ route('account.show', ['username' => $user->username]) }}">
                                    <h3>{{ $user->name }} <small>{{ $user->username }}</small></h3>
                                </a>
                                <small>
                                    <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{ $user->train_distance }}</span><span class="small font-weight-lighter">km</span>
                                    <span class="font-weight-bold pl-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}</span>
                                    <span class="font-weight-bold pl-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;{{ $user->points }}</span><span class="small font-weight-lighter">Pkt</span>
                                </small>
                                @if($user->id !== $currentUser->id && Auth::check())
                                    @if(Auth::user()->follows->where('id', $user->id)->first() === null)
                                        <a href="#" class="btn btn-sm btn-primary follow" data-userid="{{ $user->id }}" data-following="no">{{__('profile.follow')}}</a>
                                    @else
                                        <a href="#" class="btn btn-sm btn-danger follow" data-userid="{{ $user->id }}" data-following="yes">{{__('profile.unfollow')}}</a>
                                    @endif
                                    <script>
                                        window.translFollow = "{{__('profile.follow')}}";
                                        window.translUnfollow = "{{__('profile.unfollow')}}";
                                    </script>
                                @else
                                    <a href="{{ route('settings') }}" class="btn btn-sm btn-primary">{{ __('profile.settings') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-time" role="progressbar" style="width: 22342.2%;" data-valuenow="1603269554" data-valuemin="1602063077" data-valuemax="1602068477" data-now="1603269558"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="row justify-content-center mt-5">
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div><!--- /container -->
@endsection
