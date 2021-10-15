@extends('admin.layout')

@section('title', 'Veranstaltung bearbeiten')

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
                            <input id="name" type="text" class="form-control" name="name" required
                                   value="{{$event->name}}"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="hashtag" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.hashtag') }}:
                        </label>
                        <div class="col-md-6 text-center">
                            <input id="hashtag" type="text" class="form-control" name="hashtag"
                                   value="{{$event->hashtag}}" required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="host" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.host') }}:
                        </label>
                        <div class="col-md-6 text-center">
                            <input id="host" type="text" class="form-control" name="host" required
                                   value="{{$event->host}}"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('events.url') }}:</label>
                        <div class="col-md-6 text-center">
                            <input id="url" type="url" class="form-control" name="url" required
                                   value="{{$event->url}}"/>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="begin">{{ __('events.begin') }}:</label>
                            <input id="begin" type="date" class="form-control" name="begin" required
                                   value="{{$event->begin->format('Y-m-d')}}"/>
                        </div>
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="end">{{ __('events.end') }}:</label>
                            <input id="end" type="date" class="form-control" name="end" required
                                   value="{{$event->end->format('Y-m-d')}}"/>
                        </div>
                    </div>
                    <div class="form-group row mt-4">
                        <label for="nearest_station_name" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.closestStation') }}:
                        </label>
                        <div class="col-md-6 text-left" id="autocomplete-form">
                            <input type="text" id="station-autocomplete" name="nearest_station_name"
                                   class="form-control" placeholder="{{ __('stationboard.station-placeholder') }}"
                                   required value="{{$event->getTrainstation()?->name}}"/>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Speichern</button>
                </form>
            </div>
        </div>
    </div>
@endsection
