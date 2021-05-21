@extends('layouts.app')

@section('title'){{ __('menu.leaderboard') }}@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-9">
                <div class="card" id="leaderboard">
                    <div class="card-header">
                        <a href="{{route('leaderboard.month', ['date' => date('Y-m')])}}" class="float-end">
                            {{ __('leaderboard.month.title') }}
                        </a>
                        {{ __('menu.leaderboard') }}
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="main-tab" data-toggle="tab" href="#leaderboard-main"
                                   role="tab" aria-controls="home" aria-selected="true">
                                    {{ __('leaderboard.top') }} {{$users->count()}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="distance-tab" data-toggle="tab" href="#leaderboard-distance"
                                   role="tab" aria-controls="profile" aria-selected="false">
                                    {{ __('leaderboard.distance') }}
                                </a>
                            </li>
                            @isset($friends)
                                <li class="nav-item">
                                    <a class="nav-link" id="friends-tab" data-toggle="tab" href="#leaderboard-friends"
                                       role="tab" aria-controls="contact" aria-selected="false">
                                        {{ __('leaderboard.friends') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active table-responsive" id="leaderboard-main"
                                 role="tabpanel">
                                @include('leaderboard.includes.main-table', [
                                    'data'        => $users,
                                    'describedBy' => 'main-tab'
                                ])
                            </div>
                            <div class="tab-pane fade table-responsive" id="leaderboard-distance" role="tabpanel">
                                @include('leaderboard.includes.main-table', [
                                    'data'        => $distance,
                                    'describedBy' => 'distance-tab'
                                ])
                            </div>
                            @isset($friends)
                                <div class="tab-pane fade table-responsive" id="leaderboard-friends" role="tabpanel">
                                    @include('leaderboard.includes.main-table', [
                                        'data'        => $friends,
                                        'describedBy' => 'friends-tab'
                                    ])
                                </div>
                            @endisset
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
