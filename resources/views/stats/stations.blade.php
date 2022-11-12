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
                    {{__('data-may-incomplete')}}.
                    {{__('warning.insecure-performance')}}
                </div>

                <div id="map" style="min-height: 700px;"></div>
                <hr/>
                <img src="{{asset('/img/marker/dot-primary.svg')}}" style="height: 14px;"/>
                {{__('stats.stations.changed')}}
                <br/>
                <img src="{{asset('/img/marker/dot-secondary.svg')}}" style="height: 14px;"/>
                {{__('stats.stations.passed')}}
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

        let featureGroup = L.featureGroup().addTo(map);

        @foreach($usedStations as $usedStation)
        L.marker([{{ $usedStation->latitude }}, {{ $usedStation->longitude }}], {
            icon: primaryDot
        })
            .addTo(featureGroup)
            .bindPopup("{{ $usedStation->name }}");
        @endforeach

        @foreach($passedStations as $passedStation)
        L.marker([{{ $passedStation->latitude }}, {{ $passedStation->longitude }}], {
            icon: secondaryDot
        })
            .addTo(featureGroup)
            .bindPopup("{{ $passedStation->name }}");
        @endforeach

        map.fitBounds(featureGroup.getBounds());
    </script>
@endsection
