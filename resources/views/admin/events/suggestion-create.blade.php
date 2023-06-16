@php use App\Enum\EventRejectionReason; @endphp
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
                                    <input id="event-begin" type="datetime-local" class="form-control" name="begin"
                                           value="{{ $event->begin->toDateTimeLocalString() }}" required
                                    />
                                    <label for="event-begin">Begin</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <input id="event-end" type="datetime-local" class="form-control" name="end"
                                           value="{{ $event->end->clone()->endOfDay()->toDateTimeLocalString() }}"
                                           required
                                    />
                                    <label for="event-end">End</label>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            Träwelling User can only check into this event between begin and end!
                            Please be aware!
                        </small>

                        <hr/>

                        <button type="submit" class="btn btn-primary">
                            Accept & Save Event
                        </button>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{route('admin.events.suggestions.deny')}}">
                @csrf
                <input type="hidden" name="id" value="{{$event->id}}"/>

                <div class="btn-group float-end mt-2">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            Decline
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button class="btn-link dropdown-item" name="decline"
                                        value="{{EventRejectionReason::LATE}}">Too late
                                </button>
                            </li>
                            <li>
                                <button class="btn-link dropdown-item" name="decline"
                                        value="{{EventRejectionReason::DUPLICATE}}">Duplicate
                                </button>
                            </li>
                            <li>
                                <button class="btn-link dropdown-item" name="decline"
                                        value="{{EventRejectionReason::NOT_APPLICABLE}}">No Value
                                </button>
                            </li>
                            <li>
                                <button class="btn-link dropdown-item" name="decline"
                                        value="{{EventRejectionReason::DEFAULT}}">No Reason
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
