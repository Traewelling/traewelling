@extends('layouts.app')

@section('title')
{{ $event->name }}
@endsection

@section('content')
    <div class="jumbotron mt-n4" style="background-image: url({{url('/images/covers/profile-background.png')}});background-position: center;background-color: #c5232c">
        <div class="container" id="event-header">
            <div class="row justify-content-center">
                <div class="text-white col-md-8">
                    <h1 class="card-title font-bold">
                        <strong>{{ __('events.header', ['name' => $event->name]) }} <code class="text-white">#{{ $event->hashtag }}</code></strong>
                    </h1>
                    <h2 class="h2-responsive">
                        <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;{{
                            number($statuses->reduce(function($carry, $s) {
                                return $carry + $s->trainCheckin->distance;
                            }), 0)
                        }}</span><span class="small font-weight-lighter">km</span>
                        <span class="font-weight-bold pl-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(
                            secondsToDuration(
                                $statuses->reduce(function($carry, $s) {
                                    return $carry + (strtotime($s->trainCheckin->arrival) - strtotime($s->trainCheckin->departure));
                                })
                            )
                        ) !!}</span>
                        <br class="d-block d-sm-none">
                        <span class="font-weight-bold pl-sm-2"><i class="fa fa-user"></i>&nbsp;{{ $event->host }} <a href="{{ $event->url }}" class="text-white"><i class="fa fa-link text-white"></i> {{ $event->url }}</a></span>
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
            <div class="col-md-8" id="activeJourneys">
                <!-- The status cards -->
                @php($day = "---")
                @foreach($statuses as $status)
                    @php($newDay = date('Y-m-d', strtotime($status->trainCheckin->departure)))
                    @if($newDay != $day)
                        <?php
                        $day = $newDay;
                        $dtObj = new \DateTime($status->trainCheckin->departure);
                        ?>
                        <h5 class="mt-4">{{__("dates." . $dtObj->format('l')) }}, {{ $dtObj->format('j') }}. {{__("dates." . $dtObj->format('F')) }} {{ $dtObj->format('Y') }}</h5>
                    @endif

                    @include('includes.status')
                @endforeach
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            {{ $statuses->links() }}
        </div>
    </div><!--- /container -->

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
