@extends('layouts.app')

@section('title'){{ __('menu.leaderboard') }}@endsection

@section('meta-robots', 'index')
@section('meta-description', __('description.leaderboard.monthly', [
    'month' => $date->isoFormat('MMMM'),
    'year' => $date->isoFormat('YYYY')
]))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4>{{__('leaderboard.month')}} <b>{{$date->isoFormat(__('dateformat.month-and-year'))}}</b></h4>
                <hr/>
                <a href="{{route('leaderboard.month', ['date' => $date->clone()->subMonth()->format('Y-m')])}}"
                   class="btn btn-sm btn-primary float-left">
                    <em class="fas fa-arrow-left"></em> {{$date->clone()->subMonth()->isoFormat(__('dateformat.month-and-year'))}}
                </a>
                @if($date->clone()->addMonth()->isBefore(\Carbon\Carbon::now()->endOfMonth()))
                    <a href="{{route('leaderboard.month', ['date' => $date->clone()->addMonth()->format('Y-m')])}}"
                       class="btn btn-sm btn-primary float-end">
                        {{$date->clone()->addMonth()->isoFormat(__('dateformat.month-and-year'))}}
                        <em class="fas fa-arrow-right"></em>
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
                    <div class="card mb-2">
                        <div class="card-header">{{ __('leaderboard.rank') }} {{$loop->index + 1}}</div>
                        <div class="card-body text-center">
                            <div class="image-box pe-0 d-lg-flex">
                                <a href="{{ route('account.show', ['username' => $place->user->username]) }}">
                                    <img src="{{ route('account.showProfilePicture', ['username' => $place->user->username]) }}"
                                         alt="{{$place->user->username}}" style="width: 50%;">
                                </a>
                            </div>
                            <a href="{{ route('account.show', ['username' => $place->user->username]) }}"
                               style="font-size: 1.3em;">
                                {{$place->user->username}}
                            </a>


                            <table class="table text-muted">
                                <tr>
                                    <td>
                                        <em class="fas fa-dice-d20"></em>
                                        {{number($place->points, 0)}}
                                    </td>
                                    <td>
                                        <em class="fas fa-clock"></em>
                                        {{number($place->duration, 0)}}min
                                    </td>
                                    <td>
                                        <em class="fas fa-route"></em>
                                        {{number($place->distance, 0)}}km
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
                <div class="col-md-8 col-lg-7">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <table class="table table-vertical-center">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('leaderboard.rank') }}</th>
                                        <th scope="col" colspan="2">{{ __('leaderboard.user') }}</th>
                                        <th scope="col">{{ __('leaderboard.duration') }}</th>
                                        <th scope="col">{{ __('leaderboard.distance') }}</th>
                                        <th scope="col">{{ __('leaderboard.points') }}</th>
                                    </tr>
                                </thead>
                                @foreach($leaderboard->take(100) as $place)
                                    @if($loop->index < 3) @continue @endif
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>
                                            <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                                <a href="{{ route('account.show', ['username' => $place->user->username]) }}">
                                                    <img src="{{ route('account.showProfilePicture', ['username' => $place->user->username]) }}"
                                                         alt="{{$place->user->username}}">
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('account.show', ['username' => $place->user->username]) }}">
                                                {{ $place->user->username }}
                                            </a>
                                        </td>
                                        <td>{{ number( $place->duration, 0) }}<small>min</small></td>
                                        <td>{{ number( $place->distance, 0) }}<small>km</small></td>
                                        <td>{{ number( $place->points, 0) }}</td>
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

