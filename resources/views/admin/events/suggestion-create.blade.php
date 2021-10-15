@extends('admin.layout')

@section('title', 'Veranstaltungsvorschlag akzeptieren')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="col-md-6 offset-md-3">
                <form method="POST" action="{{ route('admin.events.suggestions.accept.do') }}">
                    @csrf
                    <input type="hidden" name="suggestionId" value="{{$event->id}}"/>
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">
                            {{ __('events.name') }}:
                        </label>
                        <div class="col-md-6 text-center">
                            <input id="name" type="text" class="form-control" name="name" value="{{$event->name}}"
                                   required/>
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
                            <input id="host" type="text" class="form-control" name="host" value="{{$event->host}}"
                                   required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('events.url') }}:</label>
                        <div class="col-md-6 text-center">
                            <input id="url" type="url" class="form-control" name="url" value="{{$event->url}}"
                                   required/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="begin">{{ __('events.begin') }}:</label>
                            <input id="begin" type="date" class="form-control" name="begin"
                                   value="{{ $event->begin->format("Y-m-d") }}" required/>
                        </div>
                        <div class="form-group col-sm-4 offset-sm-2 col-6">
                            <label for="end">{{ __('events.end') }}:</label>
                            <input id="end" type="date" class="form-control" name="end"
                                   value="{{ $event->end->format("Y-m-d") }}" required/>
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
                        <input type="submit" class="btn btn-primary" value="{{ __('modals.edit-confirm') }}">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
