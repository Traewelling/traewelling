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
                        <h2 class="fs-4">
                            <em class="far fa-calendar-plus"></em>
                            {{trans('events.suggest.card_title')}}
                        </h2>
                        <hr/>
                        <p>{{trans('events.suggest.card_description')}}</p>
                        @auth
                            <a href="/contribute/event-proposal" class="btn btn-primary">
                                {{trans('events.suggest.card_button')}}
                                <em class="fas fa-angle-double-right"></em>
                            </a>
                        @else
                            <p class="text-trwl bold">{{trans('auth.required')}}</p>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
