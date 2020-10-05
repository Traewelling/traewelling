@extends('layouts.app')

@section('title')
{{ __('menu.active') }}
@endsection

@section('content')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8" id="activeJourneys">
                <h4>{{ __('menu.active') }}</h4>

                <div id="map" class="embed-responsive embed-responsive-1by1"></div>
                <script>
window.addEventListener("load", () => {
    var map = L.map(document.getElementById('map'), {
        center: [50.3, 10.47],
        zoom: 5
    });

    L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
        {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: "abcd",
            maxZoom: 19
        }
    ).addTo(map);


    const statuses = [
        @foreach($statuses as $s)
            {
                id: {{$s->id}},
                origin: {{$s->trainCheckin->origin}},
                destination: {{$s->trainCheckin->destination}},
                polyline: {!! $s->trainCheckin->hafastrip->getPolyline->polyline !!}, // Stored as JSON in DB
                stops: {!! $s->trainCheckin->hafastrip->stopovers !!}, // Stored as JSON in DB
                percentage: 0,
            },
        @endforeach
    ];


    const swapC = ([lng, lat]) => [lat, lng];

    /**
     * This one is stolen from https://snipplr.com/view/25479/calculate-distance-between-two-points-with-latitude-and-longitude-coordinates/
     */
    function distance(lat1, lon1, lat2, lon2) {
        var R = 6371; // km (change this constant to get miles)
        var dLat = ((lat2 - lat1) * Math.PI) / 180;
        var dLon = ((lon2 - lon1) * Math.PI) / 180;
        var a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos((lat1 * Math.PI) / 180) *
                Math.cos((lat2 * Math.PI) / 180) *
                Math.sin(dLon / 2) *
                Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d = R * c;
        return d;
    }

    const updateMap = () => {

        /**
         * First of all: Delete all polylines that already exist on the map
         */
        for(i in map._layers) {
            if(map._layers[i]._path != undefined) {
                try {
                    map.removeLayer(map._layers[i]);
                }
                catch(e) {
                    console.log("problem with " + e + map._layers[i]);
                }
            }
        }

        const tzoffset = new Date().getTimezoneOffset() * 60000; //offset in milliseconds
        const now = new Date(Date.now() - tzoffset).toISOString();

        statuses.forEach(s => {
            let i = 0; let j = 0;
            s.stops = s.stops.filter(s => !s.cancelled)
                .map(s => {
                s.stop.id = i++ + "_" + s.stop.id;
                return s;
            });

            // Statuses with empty polylines (e.g. migrated or broken) stop right here and don't throw more errors than needed.
            if(typeof s.polyline.features == "undefined") {
                return;
            }

            s.polyline.features = s.polyline.features.map(f => {
                if(typeof f.properties.id == "undefined") {
                    return f;
                }
                f.properties.id = j++ + "_" + f.properties.id;
                return f;
            });
            const behindUs = s.stops
                .filter(
                    b =>
                        (b.departure != null && b.departure < now) ||
                        (b.arrival != null && b.arrival < now)
                )
                .map(b => b.stop.id);
            const infrontofUs = s.stops
                .filter(
                    (b => b.arrival != null && b.arrival > now) ||
                        (b => b.departure != null && b.departure > now)
                )
                .map(b => b.stop.id);

            const justInfrontofUs = s.stops[behindUs.length].stop.id;
            // The last station is relevant for us, but we can't act with it like any other station before.
            const justBehindUs = behindUs.pop();

            /**
             * This piece calculates the distance between the last and the
             *  upcoming train station, so we can interpolate between them.
             */
            let isInterestingBit = false;
            let stopDistLastToNext = 0;
            for (let i = 0; i < s.polyline.features.length - 1; i++) {
                if (s.polyline.features[i].properties.id == justBehindUs) {
                    isInterestingBit = true;
                }
                if (isInterestingBit) {
                    stopDistLastToNext += distance(
                        s.polyline.features[i].geometry.coordinates[1],
                        s.polyline.features[i].geometry.coordinates[0],
                        s.polyline.features[i + 1].geometry.coordinates[1],
                        s.polyline.features[i + 1].geometry.coordinates[0]
                    );
                }
                if (s.polyline.features[i].properties.id == justInfrontofUs) {
                    isInterestingBit = false;
                }
            }

            /**
             * Here, we describe how far we are between the last and the upcoming stop.
             */
            const stationWeJustLeft = s.stops.find(b => b.stop.id == justBehindUs);
            const leaveTime = new Date(stationWeJustLeft.departure).getTime();
            const stationNextUp = s.stops.find(b => b.stop.id == justInfrontofUs);
            const arriveTime = new Date(stationNextUp.departure).getTime();
            const nowTime = new Date().getTime();
            s.percentage = (nowTime - leaveTime) / (arriveTime - leaveTime);

            /**
             * Now, let's get through all polylines.
             */
            let sI = -1; // ID of the last visited Station

            // Since we traverse through all polygons, we need to check, if we're
            // actually on the train.
            let inTheTrain = false;

            // This is the distance that between the last station and the polygon that
            // we're traversing through. We just change the value, once we're in the
            //interesting piece of the journey.
            let polyDistSinceStop = 0;

            for (let i = 0; i < s.polyline.features.length - 1; i++) {
                if (
                    s.polyline.features[i].properties.id == s.stops[sI + 1].stop.id
                ) {
                    sI += 1;
                }

                if (s.stops[sI].stop.id.endsWith(s.origin)) {
                    inTheTrain = true;
                }

                if (inTheTrain) {
                    let isSeen = true;

                    if (justBehindUs == s.stops[sI].stop.id) {
                        // The interesting part.
                        polyDistSinceStop += distance(
                            s.polyline.features[i].geometry.coordinates[1],
                            s.polyline.features[i].geometry.coordinates[0],
                            s.polyline.features[i + 1].geometry.coordinates[1],
                            s.polyline.features[i + 1].geometry.coordinates[0]
                        );

                        isSeen =
                            polyDistSinceStop / stopDistLastToNext < s.percentage;
                    } else if (behindUs.indexOf(s.stops[sI].stop.id) > -1) {
                        isSeen = true;
                    } else if (infrontofUs.indexOf(s.stops[sI].stop.id) > -1) {
                        isSeen = false;
                    }

                    var polyline = L.polyline([
                        swapC(s.polyline.features[i].geometry.coordinates),
                        swapC(s.polyline.features[i + 1].geometry.coordinates)
                    ])
                        .setStyle({
                            color: isSeen
                                ? "rgb(192, 57, 43)"
                                : "#B8B8B8",
                            weight: 5
                        })
                        .addTo(map);
                }

                if (s.stops[sI].stop.id.endsWith(s.destination)) {
                    // After the last station on the trip, we don't need to traverse our polygons anymore.
                    break;
                }
            }
        });

    };
    updateMap();
    setInterval(() => {
        updateMap();
    }, 5 * 1000);


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
            "begin": "{{ $event->begin->format('Y-m-d') }}",
            "end": "{{ $event->end->format('Y-m-d') }}",
            "ts": {!! $event->getTrainStation() !!},
            "mapLink": "{{ route('statuses.byEvent', ['eventSlug' => $event->slug]) }}",
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
    });
});
                </script>

                <!-- The status cards -->
                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => false])
            </div>
        </div>
    </div><!--- /container -->

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
