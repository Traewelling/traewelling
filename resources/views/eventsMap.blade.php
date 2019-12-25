@extends('layouts.app')

@section('title')
    Dashboard
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
                        <span class="font-weight-bold pl-sm-2"><i class="fa fa-user"></i>&nbsp;{{ $event->host }} <a href="{{ $event->url }}" class="text-white"><i class="fa fa-link text-white"></i></a></span>
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
                <div id="map" class="embed-responsive embed-responsive-16by9"></div>
                <script>
                    window.addEventListener("load", () => {
                        var map = L.map(document.getElementById('map'), {
                            center: [50.3, 10.47],
                            zoom: 5
                        });

                        L.tileLayer(
                            "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
                            {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
                                subdomains: "abcd",
                                maxZoom: 19
                            }
                        ).addTo(map);
                        var group = new L.featureGroup();


                        const polylines = [
                            @foreach($statuses->map(function($s) { return $s->trainCheckin->getMapLines(); }) as $polyline)
                                {!! $polyline !!},
                            @endforeach
                        ];

                        polylines.forEach((p) => {
                            var polyline = L.polyline(p.map(([a, b]) => [b, a]))
                                .setStyle({color: "rgb(192, 57, 43)", weight: 5})
                                .addTo(map);
                            group.addLayer(polyline);
                        });

                        /////////// Events ///////////

                        const icon = L.divIcon({
                            html: '<i class="fa fa-calendar-day" style="line-height: 48px; font-size: 36px;"></i>',
                            iconSize: [48,48],
                            className: 'text-trwl text-center'
                        });

                        const events = [
                                @foreach($events as $event)
                            {
                                "name": "{{$event->name}}",
                                "host": "{{$event->host}}",
                                "url": "{{$event->url}}",
                                "begin": "{{ date("Y-m-d", strtotime($event->begin)) }}",
                                "end": "{{ date("Y-m-d", strtotime($event->end)) }}",
                                "ts": {!! $event->getTrainStation() !!},
                                "mapLink": "{{ route('statuses.byEvent', ['event' => $event->slug]) }}",
                                "closestLink": `{!! stationLink($event->getTrainstation()->name) !!}`
                            },
                            @endforeach
                        ];
                        events.forEach((event) => {
                            var marker = L.marker([event.ts.latitude, event.ts.longitude], {
                                title: event.name,
                                icon: icon
                            }).addTo(map);

                            marker.bindPopup(`<strong><a href="${event.url}">${event.name}</a></strong><br />
<i class="fa fa-user-clock"></i> ${event.host}<br />
<i class="fa fa-calendar-day"></i> ${event.begin} - ${event.end}<br />
<a href="${event.mapLink}">Alle Reisen zum Event anzeigen</a><br />
${event.closestLink}`);

                            group.addLayer(marker);
                        });



                        map.fitBounds(group.getBounds());
                    });
                </script>

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
    </div><!--- /container -->

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
