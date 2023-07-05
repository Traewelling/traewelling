@extends('layouts.app')

@section('title', __('menu.active'))

@section('meta-robots', 'index')
@section('meta-description', __('description.en-route'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fs-4">{{ __('menu.active') }}</h1>
            </div>
            <div class="col-md-6 mb-4" id="activeJourneys">
                <div id="map" class="embed-responsive embed-responsive-1by1" style="min-height: 600px;"></div>
                <script>
                    window.addEventListener("load", () => {
                        const statuses = [
                                @foreach($statuses as $status)
                            {
                                id: {{$status->id}},
                                origin: {{$status->trainCheckin->origin}},
                                destination: {{$status->trainCheckin->destination}},
                                polyline: {!! $status->trainCheckin->HafasTrip?->polyline?->polyline ?? '[]' !!}, // Stored as JSON in DB
                                stopovers: {!! \App\Http\Resources\StopoverResource::collection($status->trainCheckin->HafasTrip->stopoversNEW)->toJson() !!},
                                percentage: 0,
                            },
                            @endforeach
                        ];
                        const events = [
                                @foreach($events as $event)
                            {
                                "name": "{{$event->name}}",
                                "host": "{{$event->host}}",
                                "url": "{{$event->url}}",
                                "begin": "{{ $event->begin->format('Y-m-d') }}",
                                "end": "{{ $event->end->format('Y-m-d') }}",
                                @isset($event->station)
                                "ts": {!! $event->station !!},
                                @endisset
                                "mapLink": "{{ route('statuses.byEvent', ['eventSlug' => $event->slug]) }}",
                                "closestLink": "@isset($event->station) <a href=\"{{route('trains.stationboard', ['provider' => 'train', 'station' => $event->station->ibnr])}}\" class=\"text-trwl clearfix\">{{$event->station->name}}</a> @endisset"
                            },
                            @endforeach
                        ];
                        ActiveJourneys.renderMap(statuses, events);
                    });
                </script>

                <div class="row text-center fs-5 mt-3">
                    <div class="col mb-3">
                        <i class="fa-solid fa-train"></i>
                        {{$statuses->count()}}
                        {{trans_choice('active-journeys', $statuses->count())}}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @if($statuses->count() === 0)
                    <div class="alert alert-danger text-center">
                        <strong class="fs-4">{{__('empty-en-route')}}</strong>
                    </div>
                @endif

                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => false])
            </div>
        </div>
    </div>

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
