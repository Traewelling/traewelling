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
                        <input type="hidden" id="input-destination" name="destination"
                               value="{{ $hafasTrip['destination'] }}"/>
                        <input type="hidden" name="startIBNR" value="{{request()->startIBNR}}"/>
                        <input type="hidden" name="departure" value="{{request()->departure}}"/>
                        <input type="hidden" name="userId" value="{{$user->id}}"/>
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
                                                &vert; {{ __('stationboard.stop-cancelled') }}</option>
                                        @else
                                            <option
                                                value='{"destination": "{{$stop['stop']['id']}}", "arrival": "{{$stop['plannedArrival']}}"}'>
                                                {{$stop['stop']['name']}}
                                                &lt;&gt;
                                                @if(!(isset($stop['cancelled']) && $stop['arrival'] == null) && $stop['plannedArrival'] != null)
                                                    {{ __('stationboard.arr') }}
                                                    {{ \Carbon\Carbon::parse($stop['plannedArrival'])->isoFormat(__('time-format'))}}
                                                    @if(isset($stop['arrivalDelay']))
                                                        (+{{ $stop['arrivalDelay'] / 60 }})
                                                    @endif
                                                @endif
                                                &lt;&gt;
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
                                <textarea maxlength="280" class="form-control" aria-label="Haltestelle" name="body"></textarea>
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
                                    <input class="form-check-input" type="radio" name="business" id="business2"
                                           value="1">
                                    <label class="form-check-label"
                                           for="business2">{{ __('stationboard.business.business') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="business" id="business3"
                                           value="2">
                                    <label class="form-check-label"
                                           for="business3">{{ __('stationboard.business.commute') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <label for="date" class="form-label">Sichtbarkeit</label><br>
                                @foreach([0, 1, 2, 3] as $vis)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="visibility"
                                               id="visibility{{$vis}}" value="{{$vis}}" checked>
                                        <label class="form-check-label"
                                               for="visibility{{$vis}}">{{ __('status.visibility.' . $vis) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tweet"
                                           name="tweet" {{$user->twitterUrl ? '' : 'disabled'}}>
                                    <label class="form-check-label" for="tweet">
                                        Twitter
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mastodon" name="toot" {{$user->mastodonUrl ? '' : 'disabled'}}>
                                    <label class="form-check-label" for="mastodon">
                                        Mastodon
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 border-top border-bottom">
                            <div class="col">
                                <input type="checkbox" class="form-check-input" id="force" name="force">
                                <label class="form-check-label" for="force">Checkin forcieren? (Punkte werden hierfür abgezogen)</label>
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
