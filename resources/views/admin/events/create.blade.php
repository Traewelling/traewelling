@extends('admin.layout')

@section('title', 'Veranstaltung erstellen')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="col-md-6 offset-md-3">
                <form method="POST">
                    @csrf

                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.name') }}:
                        </label>
                        <div class="col-md-6 text-center">
                            <input id="name" type="text" class="form-control" name="name" required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="hashtag" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.hashtag') }}:
                        </label>
                        <div class="col-md-6 text-center">
                            <input id="hashtag" type="text" class="form-control" name="hashtag" required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="host" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.host') }}:
                        </label>
                        <div class="col-md-6 text-center">
                            <input id="host" type="text" class="form-control" name="host" required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('events.url') }}:</label>
                        <div class="col-md-6 text-center">
                            <input id="url" type="url" class="form-control" name="url" required/>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="begin">Checkin {{ __('events.begin') }}:</label>
                            <input id="begin" type="datetime-local" class="form-control" name="begin" required />
                        </div>
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="end">Checkin {{ __('events.end') }}:</label>
                            <input id="end" type="datetime-local" class="form-control" name="end" required />
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="event-begin">Event {{ __('events.begin') }}:</label>
                            <input id="event-start" type="datetime-local" class="form-control" name="event_start"
                                   required/>
                        </div>
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="event-end">Event {{ __('events.end') }}:</label>
                            <input id="event-end" type="datetime-local" class="form-control" name="event_end" required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="nearest_station_name" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.closestStation') }}:
                        </label>
                        <div class="col-md-6 text-left" id="autocomplete-form">
                            <input type="text" id="station-autocomplete" name="nearest_station_name"
                                   class="form-control" placeholder="{{ __('stationboard.station-placeholder') }}"
                                   required/>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
