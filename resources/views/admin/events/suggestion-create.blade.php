@extends('admin.layout')

@section('title', 'Accept event suggestion')

@section('content')
    <form method="POST" action="{{ route('admin.events.suggestions.accept.do') }}">
        @csrf
        <input type="hidden" name="suggestionId" value="{{$event->id}}"/>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.name') }}<span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-8 text-center">
                                <input id="name" type="text" class="form-control" name="name"
                                       required
                                       value="{{$event->name}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="hashtag" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.hashtag') }}:
                            </label>
                            <div class="col-md-8 text-center">
                                <input id="hashtag" type="text" class="form-control" name="hashtag"
                                       value="{{$event->hashtag}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="host" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.host') }}:
                            </label>
                            <div class="col-md-8 text-center">
                                <input id="host" type="text" class="form-control" name="host"
                                       value="{{$event->host}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('events.url') }}
                                :</label>
                            <div class="col-md-8 text-center">
                                <input id="url" type="url" class="form-control" name="url"
                                       value="{{$event->url}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row" id="station-autocomplete-container">
                            <label for="nearest_station_name" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.closestStation') }}:
                            </label>
                            <div class="col-md-8 text-left">
                                <input type="text" id="station-autocomplete" name="nearest_station_name"
                                       class="form-control" placeholder="{{ __('stationboard.station-placeholder') }}"
                                       value="{{$event->station?->name}}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="fs-5">
                            Checkin is possible between the following dates<span class="text-danger">*</span>:
                        </h2>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="begin" type="datetime-local" class="form-control" name="begin"
                                           value="{{ $event->begin->toDateTimeLocalString() }}"
                                           required
                                    />
                                    <label for="begin">Checkin {{ __('events.begin') }}:</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="end" type="datetime-local" class="form-control" name="end"
                                           value="{{ $event->begin->toDateTimeLocalString() }}"
                                           required
                                    />
                                    <label for="end">Checkin {{ __('events.end') }}:</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="fs-5">
                            Does the event start and end differently than the check-in times listed?
                            <i>[optionally]</i>
                        </h2>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="event-start" type="datetime-local" class="form-control"
                                           name="event_start"
                                    />
                                    <label for="event-begin">{{ __('events.begin') }}</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="event-end" type="datetime-local" class="form-control" name="event_end"
                                    />
                                    <label for="event-end">{{ __('events.end') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-regular fa-square-check"></i>
                            Accept & Save Event
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
