@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8" id="activeJourneys">

                <div id="map"></div>
                <script>
window.addEventListener("load", () => {
    var map = L.map(document.getElementById('map'), {
        center: [50.27264, 7.26469],
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


    const journeys = [
    @foreach($polylines as $p)
        {{$p}},
    @endforeach
    ];

    journeys.forEach(j => {
        const latlngs = j.map(([a, b]) => [b, a]);
        var polyline = L.polyline(latlngs)
            .setStyle({
                color: "rgb(192, 57, 43)",
                weight: 5
            })
            .addTo(map);
    });
});
                </script>

                <!-- The status cards -->
                @foreach($statuses as $status)
                    @include('includes.status')
                @endforeach
            </div>
        </div>
    </div><!--- /container -->
@endsection
