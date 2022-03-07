@extends('admin.layout')

@section('title', 'Checkin')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <form class="center" method="post" action="{{route('admin.checkin')}}">
                        @csrf
                        <input type="hidden" id="input-tripID" name="tripId" value="{{ $hafasTrip['trip_id']  }}"/>
                        <input type="hidden" name="lineName" value="{{ $hafasTrip['linename'] }}"/>
                        <input type="hidden" id="input-destination" name="destination" value="{{ $hafasTrip['destination'] }}"/>
                        <input type="hidden" name="start" value="{{request()->start}}"/>
                        <input type="hidden" name="departure" value="{{request()->departure}}"/>
                        <input type="hidden" name="user" value="{{$user->id}}"/>
                        <div class="row mt-1">
                            <div class="col">
                                <label for="destination" class="form-label">Ausstieg</label><br>
                                <select class="form-select" name="destination">
                                    <option selected>Open this select menu</option>
                                    @foreach($stopovers as $stop)
                                        @if(!\Carbon\Carbon::parse($stop['plannedArrival'])->isAfter(\Carbon\Carbon::parse(request()->departure)))
                                            @continue
                                        @endif

                                        @if(@$stop['cancelled'] == 'true' && $stop['arrival'] === null && $stop['departure'] === null)
                                            <option>{{ $stop['stop']['name'] }}
                                                | {{ __('stationboard.stop-cancelled') }}</option>
                                        @else
                                            <option
                                                value='{"destination": "{{$stop['stop']['id']}}", "arrival": "{{$stop['plannedArrival']}}"}'>
                                                {{$stop['stop']['name']}}
                                                <>
                                                @if(!(isset($stop['cancelled']) && $stop['arrival'] == null) && $stop['plannedArrival'] != null)
                                                    {{ __('stationboard.arr') }}
                                                    {{ \Carbon\Carbon::parse($stop['plannedArrival'])->isoFormat(__('time-format'))}}
                                                    @if(isset($stop['arrivalDelay']))
                                                        (+{{ $stop['arrivalDelay'] / 60 }})
                                                    @endif
                                                @endif
                                                <>
                                                Gleis {{ $stop['arrivalPlatform'] }}
                                                @if(isset($stop['plannedArrivalPlatform']) && $stop['plannedArrivalPlatform'] != $stop['arrivalPlatform'])
                                                    &nbsp;
                                                    {{ $stop['plannedArrivalPlatform'] }}
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="date" class="form-label">Status</label><br>
                                <textarea aria-valuemax="280" class="form-control" aria-label="Haltestelle" name="station"></textarea>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <label for="date" class="form-label">Reisegrund</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="business" id="business1" value="0" checked>
                                    <label class="form-check-label" for="business1">{{ __('stationboard.business.private') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="business" id="business2" value="0">
                                    <label class="form-check-label" for="business2">{{ __('stationboard.business.business') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="business" id="business3" value="0">
                                    <label class="form-check-label" for="business3">{{ __('stationboard.business.commute') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <label for="date" class="form-label">Sichtbarkeit</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="visibility" id="visibility1" value="0" checked>
                                    <label class="form-check-label" for="visibility1">{{ __('status.visibility.0') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="visibility" id="visibility2" value="1">
                                    <label class="form-check-label" for="visibility2">{{ __('status.visibility.1') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="visibility" id="visibility3" value="2">
                                    <label class="form-check-label" for="visibility3">{{ __('status.visibility.2') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="visibility" id="visibility4" value="3">
                                    <label class="form-check-label" for="visibility4">{{ __('status.visibility.3') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tweet" name="tweet">
                                    <label class="form-check-label" for="tweet">
                                        Twitter
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mastodon" name="toot">
                                    <label class="form-check-label" for="mastodon">
                                        Mastodon
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <input type="submit" class="btn btn-primary">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-4">
            @include('admin.users.usercard')
        </div>
    </div>
@endsection
