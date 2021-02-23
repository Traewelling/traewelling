@extends('layouts.app')

@section('title')
    {{ __('menu.leaderboard') }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4>{{__('leaderboard.month')}} <b>{{$date->isoFormat('MMMM YYYY')}}</b></h4>
                <hr/>
                <a href="{{route('leaderboard.month', ['date' => $date->clone()->subMonth()->format('Y-m')])}}"
                   class="btn btn-sm btn-primary float-left">
                    <i class="fas fa-arrow-left"></i> {{$date->clone()->subMonth()->isoFormat('MMMM YYYY')}}
                </a>
                @if($date->clone()->addMonth()->isBefore(\Carbon\Carbon::now()->endOfMonth()))
                    <a href="{{route('leaderboard.month', ['date' => $date->clone()->addMonth()->format('Y-m')])}}"
                       class="btn btn-sm btn-primary float-right">
                        {{$date->clone()->addMonth()->isoFormat('MMMM YYYY')}} <i class="fas fa-arrow-right"></i>
                    </a>
                @endif
                <div class="clearfix"></div>
                <hr/>
            </div>

            @if($leaderboard->count() == 0)
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center text-danger text-bold">
                            {{__('leaderboard.no_data')}}
                        </div>
                    </div>
                </div>
            @endif

            @foreach($leaderboard->take(3) as $place)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">{{ __('leaderboard.rank') }} {{$loop->index + 1}}</div>
                        <div class="card-body text-center">
                            <div class="image-box pr-0 d-none d-lg-flex">
                                <a href="{{ route('account.show', ['username' => $place['user']->username]) }}">
                                    <img src="{{ route('account.showProfilePicture', ['username' => $place['user']->username]) }}"
                                         alt="{{$place['user']->username}}" style="width: 50%;">
                                </a>
                            </div>
                            <a href="{{ route('account.show', ['username' => $place['user']->username]) }}"
                               style="font-size: 1.3em;">
                                {{$place['user']->username}}
                            </a>


                            <table class="table text-muted">
                                <tr>
                                    <td>
                                        <i class="fas fa-dice-d20"></i>
                                        {{number($place['points'], 0)}}
                                    </td>
                                    <td>
                                        <i class="fas fa-clock"></i>
                                        {{number($place['duration'], 0)}}min
                                    </td>
                                    <td>
                                        <i class="fas fa-route"></i>
                                        {{number($place['distance'], 0)}}km
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <hr/>
        @if($leaderboard->count() > 3)
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-vertical-center">
                                <thead>
                                    <tr>
                                        <td>{{ __('leaderboard.rank') }}</td>
                                        <td colspan="2">{{ __('leaderboard.user') }}</td>
                                        <td>{{ __('leaderboard.duration') }}</td>
                                        <td>{{ __('leaderboard.distance') }}</td>
                                        <td>{{ __('leaderboard.points') }}</td>
                                    </tr>
                                </thead>
                                @foreach($leaderboard->take(100) as $place)
                                    @if($loop->index < 3) @continue @endif
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>
                                            <div class="image-box pr-0 d-none d-lg-flex">
                                                <a href="{{ route('account.show', ['username' => $place['user']->username]) }}">
                                                    <img src="{{ route('account.showProfilePicture', ['username' => $place['user']->username]) }}"
                                                         alt="{{$place['user']->username}}" style="height: 75px;">
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('account.show', ['username' => $place['user']->username]) }}">
                                                {{ $place['user']->username }}
                                            </a>
                                        </td>
                                        <td>{{ number( $place['duration'], 0) }}<small>min</small></td>
                                        <td>{{ number( $place['distance'], 0) }}<small>km</small></td>
                                        <td>{{ number( $place['points'], 0) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
        @endif
    </div>
@endsection

