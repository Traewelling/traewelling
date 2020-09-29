@extends('layouts.app')

@section('title')
    {{ $hafasTrip->linename }} -> {{$destination}}
@endsection
@section('content')
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" data-linename="{{ $hafasTrip->linename }}" data-startname="{{ $hafasTrip->originStation->name }}" data-start="{{ request()->start }}" data-tripid="{{ $hafasTrip->trip_id }}">
                    <div class="float-right">
                        <a href="#" class="train-destinationrow" data-ibnr="{{$terminalStop['stop']['id']}}" data-stopname="{{$terminalStop['stop']['name']}}"><i class="fa fa-fast-forward"></i></a>
                    </div>
                    @if (file_exists(public_path('img/'.$hafasTrip->category.'.svg')))
                        <img class="product-icon" src="{{ asset('img/'.$hafasTrip->category.'.svg') }}">
                    @else
                        <i class="fa fa-train"></i>
                    @endif
                    {{ $hafasTrip->linename }} <i class="fas fa-arrow-alt-circle-right"></i> {{$hafasTrip->destinationStation->name}}
                </div>

                <div class="card-body p-0 table-responsive">
                    <table id="my-table-id" class="table table-dark table-borderless table-hover m-0" data-linename="{{ $hafasTrip->linename }}" data-startname="{{ $hafasTrip->originStation->name }}" data-start="{{ request()->start }}" data-tripid="{{ $hafasTrip->trip_id }}">
                        <thead>
                            <tr>
                                <th>{{__('stationboard.stopover')}}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        @foreach($stopovers as $stop)
                            @if(@$stop['cancelled'] == 'true')
                                <tr>
                                    <td>{{ $stop['stop']['name'] }}</td>
                                    <td><span class="text-danger">{{ __('stationboard.stop-cancelled') }}</span><br/>&nbsp;</td>
                                    <td>{{ $stop['departurePlatform'] }}</td>
                            @else
                                <tr class="train-destinationrow" data-ibnr="{{$stop['stop']['id']}}" data-stopname="{{$stop['stop']['name']}}">
                                <td>{{ $stop['stop']['name'] }}</td>
                                <td>@if($stop['arrival'] != null)
                                        {{ __('stationboard.arr') }}&nbsp;@if(isset($stop['arrivalDelay'])){{ date('H:i', strtotime($stop['arrival'])-$stop['arrivalDelay']) }}&nbsp;<small>(<span class="traindelay">+{{ $stop['arrivalDelay']/60 }}</span>)</small>@else{{ date('H:i', strtotime($stop['arrival'])) }}@endif
                                    @endif<br>
                                    @if($stop['departure'] != null)
                                        {{ __('stationboard.dep') }}&nbsp;@if(isset($stop['departureDelay'])){{ date('H:i', strtotime($stop['departure'])-$stop['departureDelay']) }}&nbsp;<small>(<span class="traindelay">+{{ $stop['departureDelay']/60 }}</span>)</small>@else{{ date('H:i', strtotime($stop['departure'])) }}@endif
                                    @endif &nbsp;
                                </td>
                                <td>{{ $stop['departurePlatform'] }}</td>
                            @endif
                            </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="checkinModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('stationboard.new-checkin')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('trains.checkin') }}" method="POST" id="checkinForm">
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">{{__('stationboard.label-message')}}</label>
                            <textarea name="body" class="form-control" id="message-text"></textarea>
                        </div>
                        @if (auth()->user()->socialProfile != null)
                            @if (auth()->user()->socialProfile->twitter_id != null)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="tweet_check" name="tweet_check">
                                <label class="custom-control-label" for="tweet_check">{{__('stationboard.check-tweet')}}</label>
                            </div>
                            @endif

                            @if (auth()->user()->socialProfile->mastodon_id != null)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="toot_check" name="toot_check">
                                <label class="custom-control-label" for="toot_check">{{__('stationboard.check-toot')}}</label>
                            </div>
                            @endif
                        @endif

                        @if($events->count() == 1)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="event_check" name="event" value="{{ $events[0]->id }}">
                                <label class="custom-control-label" for="event_check">{{ __('events.on-my-way-to', ['name' => $events[0]->name]) }}</label>
                            </div>
                        @elseif($events->count() > 1)
                            <div class="form-group">
                                <label for="event-dropdown" class="col-form-label">{{__('events.on-my-way-dropdown')}}</label>
                                <select class="form-control" id="event-dropdown" name="event">
                                    <option value="0" selected>{{ __('events.no-event-dropdown') }}</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="event" value="0">
                        @endif


                        <input type="hidden" id="business_check" name="business_check" value="">
                        <input type="hidden" id="input-tripID" name="tripID" value="">
                        <input type="hidden" id="input-destination" name="destination" value="">
                        <input type="hidden" id="input-start" name="start" value="">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('menu.abort') }}</button>
                    <button type="button" class="btn btn-primary" id="checkinButton">{{ __('stationboard.btn-checkin') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
