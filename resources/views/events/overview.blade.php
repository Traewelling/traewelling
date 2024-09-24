@extends('layouts.app')

@section('title', __('events.live'))
@section('canonical', route('events'))

@section('meta-robots', 'index')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="fs-4" id="heading-live-upcoming">
                            <em class="far fa-calendar-alt"></em>
                            {{__('events.live-and-upcoming')}}
                        </h2>
                        <hr/>
                        @if($liveAndUpcomingEvents->count() == 0)
                            <p class="text-trwl">
                                {{__('events.no-upcoming')}}
                                {{__('events.request-question')}}
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table" aria-describedby="heading-live-upcoming">
                                    <tbody>
                                        @foreach($liveAndUpcomingEvents as $event)
                                            <tr>
                                                <td>
                                                    {{$event->name}}
                                                    @isset($event->station)
                                                        <br/>
                                                        <small class="text-muted">
                                                            {{__('events.closestStation')}}:
                                                            <a href="{{route('stationboard', ['stationId' => $event->station->id, 'stationName' => $event->station->name ])}}">
                                                                {{$event->station->name}}
                                                            </a>
                                                        </small>
                                                    @endisset
                                                </td>
                                                <td>
                                                    @if($event->start->isSameDay($event->end))
                                                        {{$event->start->format('d.m.Y')}}
                                                    @else
                                                        {{$event->start->format('d.m.Y')}}
                                                        - {{$event->end->format('d.m.Y')}}
                                                    @endif
                                                    @if($event->hasExtendedCheckin)
                                                        *
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-primary btn-sm"
                                                       href="{{route('event', ['slug' => $event->slug])}}">
                                                        {{__('menu.show-more')}}
                                                        <em class="fas fa-angle-double-right"></em>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{$liveAndUpcomingEvents->links()}}
                            <small class="text-muted">
                                <sup>*</sup> {{__('events.disclaimer.extendedcheckin')}}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="fs-4" id="heading-live-upcoming">
                            <em class="far fa-calendar-plus"></em>
                            {{__('events.request')}}
                        </h2>
                        <hr/>
                        @auth
                            <form id="event-suggest">
                                <div class="form-floating mb-2">
                                    <input type="text" id="event-requester-name" name="name" class="form-control"
                                           required/>
                                    <label class="form-label" for="event-requester-name">{{__('events.name')}} *</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="text" id="event-requester-host" name="host" class="form-control"/>
                                    <label class="form-label" for="event-requester-host">{{__('events.host')}}</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-2 datepicker">
                                            <input type="date" id="event-requester-begin" name="begin"
                                                   class="form-control" required/>
                                            <label class="form-label" for="event-requester-begin">
                                                {{__('events.begin')}} *
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-2 datepicker">
                                            <input type="date" id="event-requester-end" name="end" class="form-control"
                                                   required/>
                                            <label class="form-label" for="event-requester-end">
                                                {{__('events.end')}} *
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="url" id="event-requester-url" name="url" class="form-control"/>
                                    <label class="form-label" for="event-requester-url">{{__('events.url')}}</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="text" id="event-requester-hashtag" name="hashtag"
                                           class="form-control"/>
                                    <label class="form-label"
                                           for="event-requester-hashtag">{{__('events.hashtag')}}</label>
                                </div>
                                <div class="form-floating mb-2" id="station-autocomplete-container">
                                    <input type="text" id="station-autocomplete" name="nearestStation"
                                           class="form-control"/>
                                    <label class="form-label"
                                           for="station-autocomplete">{{__('events.closestStation')}}</label>
                                </div>
                                <button type="submit" class="btn btn-primary">{{__('events.request-button')}}</button>
                            </form>

                            <hr/>
                            <small class="text-muted">{{__('events.notice')}}</small>
                        @else
                            <p class="text-trwl bold">{{__('auth.required')}}</p>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
