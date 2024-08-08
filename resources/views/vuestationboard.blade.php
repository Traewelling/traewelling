@extends('layouts.app')

@section('title', 'RIS')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7" id="station-board-new">
                <Stationboard></Stationboard>

                @if(auth()->user()->hasRole('open-beta') && !auth()->user()->can('disallow-manual-trips'))
                    <div class="text-center mt-4">
                        <hr/>
                        <p>
                            <span class="badge text-bg-info">Beta</span>
                            {{__('missing-journey')}}
                        </p>
                        <a href="{{ route('trip.create') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus"></i>
                            {{__('create-journey')}}
                        </a>
                    </div>
                @endif

                @if(isset($station) && auth()->user()?->hasRole('open-beta'))
                    <div class="accordion mt-4">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingStationMeta">
                                <button
                                    data-mdb-collapse-init
                                    class="accordion-button"
                                    type="button"
                                    data-mdb-toggle="collapse"
                                    data-mdb-target="#collapseOne"
                                    aria-expanded="true"
                                    aria-controls="collapseOne"
                                >
                                    <span class="badge bg-info me-2">Beta</span>
                                    Zeige verfügbare Daten zur Station
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show"
                                 aria-labelledby="headingStationMeta"
                                 data-mdb-parent="#accordionStationMeta">
                                <div class="accordion-body">
                                    <table class="table table-hover table-striped">
                                        <tr>
                                            <th>Bezeichnung (Fahrplan)</th>
                                            <td>{{$station->name}}</td>
                                        </tr>
                                        <tr>
                                            <th>Träwelling ID</th>
                                            <td>{{$station->id}}</td>
                                        </tr>
                                        <tr>
                                            <th>HAFAS-ID oder IBNR</th>
                                            <td>{{$station->ibnr}}</td>
                                        </tr>
                                        <tr>
                                            <th>Koordinaten</th>
                                            <td>
                                                <a href="https://www.openstreetmap.org/?mlat={{$station->latitude}}&mlon={{$station->longitude}}&zoom=14"
                                                   target="_blank">
                                                    {{$station->latitude}}, {{$station->longitude}}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Zeitzonen-Offset</th>
                                            <td>{{$station->time_offset}}</td>
                                        </tr>
                                        <tr>
                                            <th>Shift time</th>
                                            <td>{{$station->shift_time}}</td>
                                        </tr>
                                    </table>

                                    <hr/>
                                    <h2 class="fs-5">OpenData</h2>

                                    <span>Wir versuchen in nächster Zeit mehr Daten von <a
                                            href="https://www.wikidata.org/wiki/">Wikidata</a> zu beziehen.</span>
                                    @isset($station->wikidata_id)
                                        <span>
                                            Diese Station ist bereits mit einem <a
                                                href="https://www.wikidata.org/wiki/{{ $station->wikidata_id }}"
                                                target="_blank">Wikidata-Objekt</a> verknüpft.
                                        </span>

                                        <br/>
                                        <br/>

                                        <table class="table table-hover table-striped">
                                            <tr>
                                                <th>Bezeichnung</th>
                                                <td>
                                                    @foreach($station->names as $localizedName)
                                                        <span class="badge bg-secondary me-1">
                                                            {{$localizedName->language}}: {{$localizedName->name}}
                                                        </span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>IFOPT</th>
                                                <td>{{$station->ifopt}}</td>
                                            </tr>
                                            <tr>
                                                <th>RL100</th>
                                                <td>{{$station->rilIdentifier}}</td>
                                            </tr>
                                        </table>

                                        <a href="https://www.wikidata.org/wiki/{{ $station->wikidata_id }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-secondary float-end">
                                            <i class="fa-solid fa-edit"></i>
                                            Bearbeiten
                                        </a>

                                        <small>
                                            Fehler gefunden? Auf Wikidata bearbeiten!
                                            Derzeit aktualisieren wir die Daten von Wikidata nur sehr unregelmäßig.
                                            Es kann daher lange dauern, bis deine Änderungen hier angezeigt
                                            werden.
                                        </small>
                                    @else
                                        Diese Station ist noch nicht mit einem Wikidata-Objekt verknüpft,
                                        daher können wir aktuell keine weiteren Informationen anzeigen.
                                        <br/><br/>
                                        Du kannst helfen, indem du die Stationsdaten bei Wikidata pflegt.
                                        Wichtig sind insbesondere Identifier wie die IBNR, IFOPT oder das RL-100
                                        Kürzel.
                                    @endisset
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
