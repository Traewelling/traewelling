@extends('layouts.app')

@section('title')
    {{ __('menu.leaderboard') }}
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card" id="leaderboard">
                    <div class="card-header">
                        {{ __('menu.leaderboard') }}
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#top20" role="tab"
                                   aria-controls="home"
                                   aria-selected="true">{{ __('leaderboard.top') }} {{$usersCount}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#top20k" role="tab"
                                   aria-controls="profile" aria-selected="false">{{ __('leaderboard.distance') }}</a>
                            </li>
                            @if($friends != null)
                                <li class="nav-item">
                                    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#top20f" role="tab"
                                       aria-controls="contact" aria-selected="false">{{ __('leaderboard.friends') }}</a>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active table-responsive" id="top20" role="tabpanel"
                                 aria-labelledby="home-tab">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <td>{{ __('leaderboard.rank') }}</td>
                                        <td>{{ __('leaderboard.user') }}</td>
                                        <td>{{ __('leaderboard.duration') }}</td>
                                        <td>{{ __('leaderboard.distance') }}</td>
                                        <td>{{ __('leaderboard.averagespeed') }}</td>
                                        <td>{{ __('leaderboard.points') }}</td>
                                    </tr>
                                    </thead>
                                    @foreach($users as $key=>$user)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>
                                                <a href="{{ route('account.show', ['username' => $user->username]) }}">{{ $user->username }}</a>
                                            </td>
                                            <td>{!! durationToSpan(secondsToDuration(60 * $user->train_duration)) !!}</td>
                                            <td>{{ number($user->train_distance) }}<small>km</small></td>
                                            <td>{{ number($user->averageSpeed) }}<small>km/h</small></td>
                                            <td>{{ $user->points }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="tab-pane fade table-responsive" id="top20k" role="tabpanel"
                                 aria-labelledby="profile-tab">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <td>{{ __('leaderboard.rank') }}</td>
                                        <td>{{ __('leaderboard.user') }}</td>
                                        <td>{{ __('leaderboard.duration') }}</td>
                                        <td>{{ __('leaderboard.distance') }}</td>
                                        <td>{{ __('leaderboard.averagespeed') }}</td>
                                        <td>{{ __('leaderboard.points') }}</td>
                                    </tr>
                                    </thead>
                                    @foreach($kilometers as $key=>$user)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>
                                                <a href="{{ route('account.show', ['username' => $user->username]) }}">{{ $user->username }}</a>
                                            </td>
                                            <td>{!! durationToSpan(secondsToDuration(60 * $user->train_duration)) !!}</td>
                                            <td>{{ number($user->train_distance) }}<small>km</small></td>
                                            <td>{{ number($user->averageSpeed) }}<small>km/h</small></td>
                                            <td>{{ $user->points }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                            @if($friends != null)
                                <div class="tab-pane fade table-responsive" id="top20f" role="tabpanel"
                                     aria-labelledby="contact-tab">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <td>{{ __('leaderboard.rank') }}</td>
                                            <td>{{ __('leaderboard.user') }}</td>
                                            <td>{{ __('leaderboard.duration') }}</td>
                                            <td>{{ __('leaderboard.distance') }}</td>
                                            <td>{{ __('leaderboard.points') }}</td>
                                        </tr>
                                        </thead>
                                        @foreach($friends as $key=>$user)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>
                                                    <a href="{{ route('account.show', ['username' => $user->username]) }}">{{ $user->username }}</a>
                                                </td>
                                                <td>{!! durationToSpan(secondsToDuration(60 * $user->train_duration)) !!}</td>
                                                <td>{{ number($user->train_distance) }}<small>km</small></td>
                                                <td>{{ $user->points }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--- /container -->
@endsection
