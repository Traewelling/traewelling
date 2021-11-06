@extends('layouts.app')

@section('title', $event->name)

@section('content')
    <div class="px-4 py-5 mt-n4"
         style="background-image: url({{url('/images/covers/profile-background.png')}});background-position: center;background-color: #c5232c">
        <div class="container" id="event-header">
            <div class="row justify-content-center">
                <div class="text-white col-md-8 col-lg-7">
                    <h1 class="card-title font-bold">
                        <strong>
                            {{ __('events.header', ['name' => $event->name]) }}
                            <code class="text-white">#{{ $event->hashtag }}</code>
                        </strong>
                    </h1>
                    <h2 class="h2-responsive">
                        <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{
                            number($statuses->reduce(function($carry, $s) {
                                return $carry + $s->trainCheckin->distance / 1000;
                            }), 0)
                        }}</span><span class="small font-weight-lighter">km</span>
                        <span class="font-weight-bold ps-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(
                            secondsToDuration(
                                $statuses->reduce(function($carry, $s) {
                                    return $carry + (strtotime($s->trainCheckin->arrival) - strtotime($s->trainCheckin->departure));
                                })
                            )
                        ) !!}</span>
                        <br class="d-block d-sm-none">
                        <span class="font-weight-bold ps-sm-2">
                            <i class="fa fa-user"></i>&nbsp;{{ $event->host }}
                        </span>
                        <span class="font-weight-bold ps-sm-2">
                            <i class="fa fa-link text-white"></i>&nbsp;<a href="{{ $event->url }}"
                                                                          class="text-white">{{ parse_url($event->url)['host'] }}</a>
                        </span>
                    </h2>
                    <h2 class="h2-responsive">
                        <span class="font-weight-bold"><i class="fa fa-train"></i></span>
                        <span class="font-weight-bold">{!! stationLink($event->getTrainstation()->name, "text-white") !!}</span>
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7" id="activeJourneys">
                @foreach($statuses as $status)
                    @include('includes.status')
                @endforeach
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            {{ $statuses->links() }}
        </div>
    </div>

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
