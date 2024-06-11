@extends('admin.layout')

@section('title', 'Accept event suggestion')

@section('content')
    <form method="POST" action="{{ route('admin.events.suggestions.accept.do') }}">
        @csrf
        <input type="hidden" name="suggestionId" value="{{$eventSuggestion->id}}"/>
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
                                       value="{{$eventSuggestion->name}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="hashtag" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.hashtag') }}:
                            </label>
                            <div class="col-md-8 text-center">
                                <input id="hashtag" type="text" class="form-control" name="hashtag"
                                       value="{{$eventSuggestion->hashtag}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="host" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.host') }}:
                            </label>
                            <div class="col-md-8 text-center">
                                <input id="host" type="text" class="form-control" name="host"
                                       value="{{$eventSuggestion->host}}"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('events.url') }}
                                :</label>
                            <div class="col-md-8 text-center">
                                <input id="url" type="url" class="form-control" name="url"
                                       value="{{$eventSuggestion->url}}"
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
                                       value="{{$eventSuggestion->station?->name}}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h2 class="fs-5">
                            Parallel Events
                        </h2>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($parallelEvents as $parallelEvent)
                                <li class="list-group-item">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{route('admin.events.edit', ['id' => $parallelEvent->id])}}">{{ $parallelEvent->id }}</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{route('event', ['slug' => $parallelEvent->slug])}}">
                                                {{ $parallelEvent->name }} <i
                                                    class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item">#{{ $parallelEvent->hashtag}}</li>
                                        <li class="breadcrumb-item">{{ $parallelEvent->station?->name}}</li>
                                        <li class="breadcrumb-item">
                                            <code>similarity: {{ round($parallelEvent->similarity, 1) }} %</code></li>
                                    </ol>
                                    {{ $parallelEvent->checkin_start->format('Y-m-d H:i') }}
                                    - {{ $parallelEvent->checkin_end->format('Y-m-d H:i') }}
                                    <code>Created at: {{ $parallelEvent->created_at }}</code>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h2 class="fs-5">
                            Checkin is possible between the following dates<span class="text-danger">*</span>:
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="begin" type="datetime-local" class="form-control" name="begin"
                                           value="{{ $eventSuggestion->begin->startOfDay()->toDateTimeLocalString() }}"
                                           required
                                    />
                                    <label for="begin">Checkin {{ __('events.begin') }}:</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="end" type="datetime-local" class="form-control" name="end"
                                           value="{{ $eventSuggestion->end->endOfDay()->setSeconds(0)->toDateTimeLocalString() }}"
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
                    <div class="card-header">
                        <h2 class="fs-5">
                            Does the event start and/or end differ?
                            <small class="fst-italic">[optional]</small>
                        </h2>
                    </div>
                    <div class="card-body">
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
    @can('deny-events')
        <form method="POST" action="{{route('admin.events.suggestions.deny')}}">
            @csrf
            <input type="hidden" name="id" value="{{$eventSuggestion->id}}"/>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <x-event-rejection-button/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endcan
@endsection
