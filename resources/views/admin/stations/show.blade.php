@php use App\Enum\Wikidata\Property; @endphp
@extends('admin.layout')

@section('title', 'Station - ' . $station->name)

@section('content')

    <div class="row">
        <div class="col-md-5">
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
                            <th>IBNR</th>
                            <td>{{ $station->ibnr }}</td>
                        </tr>
                        <tr>
                            <th>IFOPT</th>
                            <td>{{ $station->ifopt }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-4">Wikidata</h2>

                    @isset($station->wikidataEntity)

                        <table class="table">
                            <tr>
                                <th>Wikidata ID</th>
                                <td>
                                    <a href="https://www.wikidata.org/wiki/{{ $station->wikidata_id }}"
                                       target="{{ $station->wikidata_id }}"
                                    >
                                        {{ $station->wikidata_id }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Labels</th>
                                <td>
                                    @foreach($station->wikidataEntity->data['labels'] ?? [] as $language => $label)
                                        <i>{{$language}}</i>: {{$label}}<br/>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>IBNR</th>
                                <td>
                                    @foreach($station->wikidataEntity->data['statements'][Property::IBNR->value] ?? [] as $statement)
                                        <a href="https://reiseauskunft.bahn.de/bin/bhftafel.exe/en?input={{ $statement['value']['content'] ?? '' }}&boardType=dep&time=actual&productsDefault=1111101&start=yes"
                                           target="{{ $statement['value']['content'] ?? '' }}"
                                        >
                                            {{ $statement['value']['content'] ?? '' }}
                                        </a>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>IFOPT</th>
                                <td>
                                    @foreach($station->wikidataEntity->data['statements'][Property::IFOPT->value] ?? [] as $statement)
                                        <a href="https://www.fahrplanauskunft-mv.de/vmvsl3plus/departureMonitor?formik=origin%3D{{ urlencode($statement['value']['content'] ?? '') }}&lng=en"
                                           target="{{ $statement['value']['content'] ?? '' }}"
                                        >
                                            {{ $statement['value']['content'] ?? '' }}
                                        </a>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>Ril100</th>
                                <td>
                                    @foreach($station->wikidataEntity->data['statements'][Property::DEUTSCHE_BAHN_STATION_CODE->value] ?? [] as $statement)
                                        <a href="https://iris.noncd.db.de/wbt/js/index.html?bhf={{ $statement['value']['content'] ?? '' }}&zeilen=50&seclang=en"
                                           target="{{ $statement['value']['content'] ?? '' }}"
                                        >
                                            {{ $statement['value']['content'] ?? '' }}
                                        </a>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>Last fetched</th>
                                <td>
                                    {{ $station->wikidataEntity->last_updated_at?->format('Y-m-d H:i:s') ?? 'never' }}
                                </td>
                            </tr>
                        </table>
                    @else
                        <span class="fw-bold text-danger">No Wikidata entity for this station linked.</span>
                        <hr/>
                        <form class="wikidata-link">
                            <input type="hidden" name="id" value="{{$station->id}}"/>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="wikidata_id"
                                       placeholder="Link Wikidata ID">
                                <label for="wikidata_id">Link Wikidata ID</label>
                            </div>
                        </form>
                        <script>
                            document.querySelector('form.wikidata-link').addEventListener('submit', function (event) {
                                event.preventDefault();

                                const id         = document.querySelector('form.wikidata-link input[name="id"]').value;
                                const wikidataId = document.querySelector('form.wikidata-link input[name="wikidata_id"]').value;

                                fetch('/api/v1/station/' + id, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        wikidata_id: wikidataId
                                    })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.data) {
                                            window.location.reload();
                                        } else {
                                            alert('Error linking Wikidata ID');
                                        }
                                    });
                            });
                        </script>
                    @endisset
                </div>
            </div>

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
        </div>
    </div>

@endsection
