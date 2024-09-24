@extends('layouts.app')

@section('title', $event->name)
@section('canonical', route('event', ['slug' => $event->slug]))

@section('content')
    <div class="px-4 py-5 mt-n4 profile-banner">
        <div class="container" id="event-header">
            <div class="row justify-content-center">
                <div class="text-white col-md-8 col-lg-7">
                    <h1 class="card-title font-bold">
                        <strong>
                            {{ __('events.header', ['name' => $event->name]) }}
                            @isset($event->hashtag)
                                <code class="text-white">#{{ $event->hashtag }}</code>
                            @endisset
                        </strong>
                    </h1>
                    <h2 class="h2-responsive">
                        @if($event->start->isSameDay($event->end))
                            {{$event->start->format('d.m.Y')}}
                        @else
                            {{$event->start->format('d.m.Y')}}
                            - {{$event->end->format('d.m.Y')}}
                        @endif
                    </h2>
                    <h2 class="h2-responsive">
                        <span class="font-weight-bold">
                            <i class="fa fa-route d-inline"></i>
                            {{ number($event->totalDistance / 1000, 0) }}
                        </span>
                        <span class="small font-weight-lighter">km</span>
                        <span class="font-weight-bold ps-sm-2">
                            <i class="fa fa-stopwatch d-inline"></i>
                            {!! durationToSpan(secondsToDuration($event->totalDuration * 60)) !!}
                        </span>
                        <br class="d-block d-sm-none">
                        @isset($event->host)
                            <span class="font-weight-bold ps-sm-2">
                                <i class="fa fa-user"></i>&nbsp;{{ $event->host }}
                            </span>
                        @endisset
                        @isset($event->url)
                            <span class="font-weight-bold ps-sm-2">
                                <i class="fa fa-link text-white"></i>&nbsp;
                                <a href="{{ $event->url }}" class="text-white" target="_blank">
                                    {{ parse_url($event->url)['host'] }}
                                </a>
                            </span>
                        @endisset
                    </h2>
                    @isset($event->station)
                        <h2 class="h2-responsive">
                            <span class="font-weight-bold"><i class="fa fa-train"></i></span>
                            <span class="font-weight-bold">
                                 <a href="{{route('stationboard', ['stationId' => $event->station->id, 'stationName' => $event->station->name])}}"
                                    class="text-white">
                                    {{$event->station->name}}
                                 </a>
                            </span>
                        </h2>
                    @endisset
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7" id="activeJourneys">
                @foreach($statuses as $status)
                    @include('includes.status')
                @endforeach
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            {{ $statuses->links() }}

            <small class="text-muted">
                <sup>1</sup> {{__('events.disclaimer.organizer')}}
                <sup>2</sup> {{__('events.disclaimer.source')}}
                <sup>3</sup> {{__('events.disclaimer.warranty')}}
            </small>
        </div>
    </div>

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
