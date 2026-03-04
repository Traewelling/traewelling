@php use App\Http\Controllers\Backend\User\ProfilePictureController; @endphp
@extends('admin.layout')

@section('title', 'Segment ' . $segment->id)

@php
    /** @var \App\Models\RouteSegment $segment */
    $coordinates = $segment->getCoordinates();
    $points = [];
    foreach ($coordinates as $coord) {
        $points[] = [(float)$coord->latitude, (float)$coord->longitude];
    }
@endphp

@section('content')
    <div class="row">
        <!-- links to all trips in use -->
        <div class="w-100 accordion mb-2" id="tripsAccordion">
            <div class="accordion-item">
                <h5 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Trips using this Segment: {{ $segment->trips->count()  }}
                    </button>
                </h5>
                <div class="flex flex-wrap accordion-collapse collapse p-2" id="collapseOne" aria-labelledby="headingOne" data-bs-parent="#tripsAccordion">
                    @foreach($segment->trips as $trip)
                        <a class="btn btn-primary btn-sm mt-1" href="{{ route('admin.trips.show', $trip) }}">Trip #{{ $trip->id }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th colspan="2">Segment Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>From Station</td><td>{{ $segment->fromStation->name  }}</td>
                    </tr>
                    <tr>
                        <td>To Station</td><td>{{ $segment->toStation->name  }}</td>
                    </tr>
                    <tr>
                        <td>Distance</td><td>{{ $segment->distance/1000 }} km</td>
                    </tr>
                    <tr>
                        <td>Duration</td><td>{{ gmdate("H:i:s", $segment->duration) }}</td>
                    </tr>
                    <tr>
                        <td>Polyline</td><td><input class="input w-100" value="{{ $segment->polyline }}" disabled></td>
                    </tr>
                    <tr>
                        <td>Polyline Precision</td><td>{{ $segment->polyline_precision }}</td>
                    </tr>
                    <tr>
                        <td>Path Type</td><td>{{ $segment->path_type }}</td>
                    </tr>
                    <tr>
                        <td>Stopovers</td><td>{{ $segment->stopOvers()->count() }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div id="map" style="height: 50vh;"></div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const latLngs = @json($points);
            const map = L.map('map');
            setTilingLayer('open-railway-map', map);
            const polyline = L.polyline(latLngs, {color: 'blue'}).addTo(map);
            map.fitBounds(polyline.getBounds());
        });
    </script>
@endsection
