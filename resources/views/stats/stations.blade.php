@extends('layouts.app')

@section('title', __('stats'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="map" style="min-height: 700px;"></div>
            </div>
        </div>
    </div>

    <script>
        let map = L.map('map').setView([50.3, 10.47], 5);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let primaryDot = L.icon({
            iconUrl: '/img/marker/dot-primary.svg',
            iconSize: [10, 10],
        });

        @foreach($usedStations as $usedStation)
        L.marker([{{ $usedStation->latitude }}, {{ $usedStation->longitude }}], {
            icon: primaryDot
        })
            .addTo(map)
            .bindPopup("{{ $usedStation->name }}");
        @endforeach
    </script>
@endsection
