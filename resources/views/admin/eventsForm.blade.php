@extends('layouts.admin')

@section('title')
    Event: {{$event->name}}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            @if($isNew)
                <h4>Neues Event anlegen</h4>
            @else
                <h4>Event bearbeiten</h4>
            @endif
        </div>
        <div class="card-body">
            <div class="col-md-6 offset-md-3">
                <form class="container" method="POST" action="{{ ($event->id == 0) ? route('events.store') : route('events.update', ['slug' => $event->slug]) }}">
            @csrf
            @if($event->id != 0)
                @method("PUT")
            @endif

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('events.name') }}:</label>
                <div class="col-md-6 text-center">
                    <input id="name" type="text" class="form-control" name="name" value="{{$event->name}}" required autofocus>
                </div>
            </div>
            <div class="form-group row">
                <label for="hashtag" class="col-md-4 col-form-label text-md-right">{{ __('events.hashtag') }}:</label>
                <div class="col-md-6 text-center">
                    <input id="hashtag" type="text" class="form-control" name="hashtag" value="{{$event->hashtag}}" required autofocus>
                </div>
            </div>
            <div class="form-group row">
                <label for="host" class="col-md-4 col-form-label text-md-right">{{ __('events.host') }}:</label>
                <div class="col-md-6 text-center">
                    <input id="host" type="text" class="form-control" name="host" value="{{$event->host}}" required autofocus>
                </div>
            </div>
            <div class="form-group row">
                <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('events.url') }}:</label>
                <div class="col-md-6 text-center">
                    <input id="url" type="url" class="form-control" name="url" value="{{$event->url}}" required autofocus>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4 offset-sm-2 col-6">
                    <label for="begin">{{ __('events.begin') }}:</label>
                    <input id="begin" type="date" class="form-control" name="begin" value="{{ (new Carbon\Carbon($event->begin))->format("Y-m-d") }}" required autofocus>
                </div>
                <div class="form-group col-sm-4 offset-sm-2 col-6">
                    <label for="end">{{ __('events.end') }}:</label>
                    <input id="end" type="date" class="form-control" name="end" value="{{ (new Carbon\Carbon($event->end))->format("Y-m-d") }}" required autofocus>
                </div>
            </div>
            <div class="form-group row">
                <label for="nearest_station_name" class="col-md-4 col-form-label text-md-right">{{ __('events.closestStation') }}:</label>
                <div class="col-md-6 text-left" id="autocomplete-form">
                    <input type="text" id="station-autocomplete" name="nearest_station_name" class="form-control" placeholder="{{ __('stationboard.station-placeholder') }}" value="{{ $event->getTrainStation()->name }}" required>
                </div>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="{{ __('modals.edit-confirm') }}">

                @if($event->id != 0)
                    <a href="#" class="btn btn-danger" role="button" data-toggle="modal" data-target="#delete-modal-{{ $event->id }}">
                        {{ __('modals.delete-confirm') }}
                    </a>
                @endif
            </div>
        </form>
            </div>
        </div>
    </div>

    @if($event->id != 0)
    <div class="modal fade" tabindex="-1" role="dialog" id="delete-modal-{{ $event->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.deleteEvent-title')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    {!! __('modals.deleteEvent-body', ['name' => $event->name]) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{__('menu.abort')}}</button>
                    <a href="{{ URL::signedRoute('events.delete', ['slug' => $event->slug]) }}" class="btn btn-danger" id="modal-delete">{{__('modals.delete-confirm')}}</a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @endif
@endsection
