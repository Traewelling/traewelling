@extends('admin.layout')

@section('title', 'Veranstaltungsvorschlag akzeptieren')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.events.suggestions.accept.do') }}">
                        @csrf
                        <input type="hidden" name="suggestionId" value="{{$event->id}}"/>

                        <div class="form-floating mb-2">
                            <input id="event-title" type="text" class="form-control" name="name"
                                   value="{{$event->name}}"
                                   required/>
                            <label for="event-title">Title *</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input id="event-hashtag" type="text" class="form-control" name="hashtag"
                                   value="{{$event->hashtag}}" required/>
                            <label for="event-hashtag">Hashtag (without # character) *</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input id="event-host" type="text" class="form-control" name="host" value="{{$event->host}}"
                                   required/>
                            <label for="event-host">Organizer *</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input id="event-url" type="url" class="form-control" name="url" value="{{$event->url}}"/>
                            <label for="event-url">URL (optional)</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input type="text" id="event-station" name="nearest_station_name"
                                   class="form-control" placeholder="Closest Träwelling station"
                                   value="{{$event->nearest_station_name}}"
                            />
                            <label for="event-station">Closest Träwelling station (optional)</label>
                        </div>

                        <hr/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <input id="checkin-begin" type="datetime-local" class="form-control" name="begin"
                                           value="{{ $event->begin->toDateTimeLocalString() }}" required
                                    />
                                    <label for="checkin-begin">Checkin Begin</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <input id="checkin-end" type="datetime-local" class="form-control" name="end"
                                           value="{{ $event->end->clone()->endOfDay()->toDateTimeLocalString() }}"
                                           required
                                    />
                                    <label for="checkin-end">Checkin End</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <input id="event-start"
                                           type="datetime-local" class="form-control" name="event_start"
                                           value="{{ $event->begin->toDateTimeLocalString() }}" required
                                    />
                                    <label for="event-start">Event Begin</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <input id="event-end" type="datetime-local" class="form-control" name="event_end"
                                           value="{{ $event->end->clone()->endOfDay()->toDateTimeLocalString() }}"
                                           required
                                    />
                                    <label for="event-end">Event End</label>
                                </div>
                            </div>
                        </div>

                        <hr/>

                        <button type="submit" class="btn btn-primary">
                            Accept & Save Event
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
