@extends('admin.layout')
@php
    /** @var \App\Models\Station $station */
@endphp

@section('title', 'Station - ' . $station->name)

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Träwelling ID</th>
                            <td>{{ $station->id }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $station->name }}</td>
                        </tr>
                        <tr>
                            <th>Relevance</th>
                            <td>{{ $station->relevance }}</td>
                        </tr>
                        <tr>
                            <th>Timezone offset</th>
                            <td>
                                {{ $station->time_offset ?? 'null' }}
                                <a class="float-end btn btn-sm btn-outline-primary"
                                   onclick="resetTimeOffset({{$station->id}})"
                                >
                                    Reset Time Offset
                                </a>
                                <script>
                                    function resetTimeOffset(stationId) {
                                        fetch('/api/v1/stations/' + stationId, {
                                            method: 'PUT',
                                            body: JSON.stringify({time_offset: null}),
                                            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}
                                        }).then(function () {
                                            location.reload();
                                        })
                                    }
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Identifier
                            </th>
                            <td>
                                <table class="table table-bordered">
                                    @foreach($station->stationIdentifiers->sortByDesc('relevance') as $identifier)
                                        <tr>
                                            <td>{{ $identifier->type }}</td>
                                            <td>
                                                <a href="{{$identifier->getRawTransitousApiLinkToDepartures()}}"
                                                   target="_blank">
                                                    {{ $identifier->identifier }}
                                                </a>
                                            </td>
                                            <td>{{ $identifier->name }}</td>
                                            <td>{{ $identifier->origin }}</td>
                                            <td>{{ $identifier->relevance }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th>Created at</th>
                            <td>
                                {{ $station->created_at?->toIso8601String() }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body" style="padding: 0;">
                    <div id="map" style="height: 300px;"></div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const map = L.map('map').setView([{{ $station->latitude }}, {{ $station->longitude }}], 13);
                        setTilingLayer('open-railway-map', map);

                        const iconHtml = '<i class="fas fa-map-marker-alt fa-2x" style="color: red;"></i>';
                        const customIcon = L.divIcon({
                            html: iconHtml,
                            className: '',
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        });

                        L.marker([{{ $station->latitude }}, {{ $station->longitude }}], {icon: customIcon}).addTo(map)
                            .bindPopup('{{ $station->name }}')
                            .openPopup();
                    });
                </script>
            </div>


            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-4">Nearby Stations</h2>

                    <table class="table table-striped table-hover">
                        <tbody>
                        @foreach($nearbyStations as $nearbyStation)
                            <tr>
                                <td>
                                    [{{ $nearbyStation->id }}]
                                    <a href="{{ route('admin.station', ['id' => $nearbyStation->id]) }}">
                                        {{ $nearbyStation->name }}
                                    </a>
                                    <br>
                                    <small>
                                        ({{ number_format($nearbyStation->distance, 3, ',', '.') }} km)
                                    </small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info"
                                            onclick="mergeStations({{ $station->id }}, {{ $nearbyStation->id }})"
                                            title="Merge {{ $station->id }} into {{ $nearbyStation->id }}"
                                    >
                                        {{ $station->id }} → {{ $nearbyStation->id }}
                                    </button>
                                    <br>
                                    <button class="btn btn-sm btn-outline-info"
                                            onclick="mergeStations({{ $nearbyStation->id }}, {{ $station->id }})"
                                    >
                                        {{ $nearbyStation->id }} → {{ $station->id }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-4">Latest checkins</h2>

                    <table class="table table-striped table-hover">
                        <tbody>
                        @foreach($latestCheckins as $checkin)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.statuses.edit', ['statusId' => $checkin->status_id]) }}">
                                        {{ $checkin->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="/admin/users/{{$checkin->user->id}}">
                                        {{ $checkin->user->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="/admin/stations/{{$checkin->originStopover->station->id}}">
                                        {{ $checkin->originStopover->station->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="/admin/stations/{{$checkin->destinationStopover->station->id}}">
                                        {{ $checkin->destinationStopover->station->name }}
                                    </a>
                                </td>
                                <td>
                                    {{ $checkin->created_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mergeStations(oldStationId, newStationId) {
            fetch('/api/v1/station/' + oldStationId + '/merge/' + newStationId, {
                method: 'PUT',
            }).then(response => {
                if (response.status === 200) {
                    notyf.success('Stations merged successfully');
                    location.href = '/admin/stations/' + newStationId;
                    return;
                }
                response.json().then(data => {
                    notyf.error(data.message ?? 'Something went wrong. Please try again later.');
                });
            });
        }
    </script>

@endsection
