@extends('layouts.app')

@section('title', __('stats.stations'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fs-4">{{__('stats.stations')}}</h1>
                <div class="alert alert-danger">
                    {{__('experimental-feature')}}
                    -
                    {{__('data-may-incomplete')}}
                </div>

                <div id="map" style="min-height: 700px;"></div>
            </div>
        </div>
    </div>

    <script>
        let map = L.map('map').setView([50.3, 10.47], 5);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let primaryDot   = L.icon({
            iconUrl: '/img/marker/dot-primary.svg',
            iconSize: [10, 10],
        });
        let secondaryDot = L.icon({
            iconUrl: '/img/marker/dot-secondary.svg',
            iconSize: [10, 10],
        });

        @foreach($usedStations as $usedStation)
        L.marker([{{ $usedStation->latitude }}, {{ $usedStation->longitude }}], {
            icon: primaryDot
        })
            .addTo(map)
            .bindPopup("{{ $usedStation->name }}");
        @endforeach

        @foreach($passedStations as $passedStation)
        L.marker([{{ $passedStation->latitude }}, {{ $passedStation->longitude }}], {
            icon: secondaryDot
        })
            .addTo(map)
            .bindPopup("{{ $passedStation->name }}");
        @endforeach
    </script>
@endsection
