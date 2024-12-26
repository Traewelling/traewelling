@php use App\Enum\Wikidata\Property; @endphp
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
                            <th>Wikidata ID</th>
                            <td>
                                <a href="https://www.wikidata.org/wiki/{{ $station->wikidata_id }}"
                                   target="{{ $station->wikidata_id }}"
                                >
                                    {{ $station->wikidata_id }}
                                </a>

                                <a class="float-end btn btn-sm btn-outline-primary"
                                   onclick="fetch('/admin/stations/{{ $station->id }}/wikidata', {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}
                       }).then(function() {location.reload()})"
                                >
                                    Fetch <small>experimental!</small>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>IBNR</th>
                            <td>
                                <a href="https://reiseauskunft.bahn.de/bin/bhftafel.exe/en?input={{ $station->ibnr ?? '' }}&boardType=dep&time=actual&productsDefault=1111101&start=yes"
                                   target="{{ $station->ibnr ?? '' }}"
                                >
                                    {{ $station->ibnr }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>IFOPT</th>
                            <td>
                                {{ $station->ifopt }}
                            </td>
                        </tr>
                        <tr>
                            <th>RL100</th>
                            <td>
                                <a href="https://iris.noncd.db.de/wbt/js/index.html?bhf={{ $station->rilIdentifier ?? '' }}&zeilen=50&seclang=en"
                                   target="{{ $station->rilIdentifier ?? '' }}"
                                >
                                    {{ $station->rilIdentifier }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <a href="https://www.wikidata.org/wiki/Property:P2561"
                                   target="P2561">
                                    Names
                                </a>
                            </th>
                            <td>
                                <table class="table table-bordered">
                                    @foreach($station->names as $name)
                                        <tr>
                                            <td>{{ $name->language }}</td>
                                            <td>{{ $name->name }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-4">Map view</h2>
                    <div id="map" style="height: 200px;"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {

                            const map = L.map('map').setView([{{ $station->latitude }}, {{ $station->longitude }}], 13);
                            setTilingLayer('open-railway-map', map);

                            L.marker([{{ $station->latitude }}, {{ $station->longitude }}]).addTo(map)
                                .bindPopup('{{ $station->name }}')
                                .openPopup();
                        });
                    </script>
                </div>
            </div>

            @isset($station->ifopt_a)
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="fs-4">Stations with same Ifopt</h2>

                        <table class="table table-striped table-hover">
                            @foreach($stationsWithSameIfopt as $stationWithSameIfopt)
                                <tr>
                                    <td>
                                        {{ $stationWithSameIfopt->id }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.station', ['id' => $stationWithSameIfopt->id]) }}">
                                            {{ $stationWithSameIfopt->name }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $stationWithSameIfopt->distanceToSimilarStation }}m
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endisset
        </div>
    </div>

@endsection
