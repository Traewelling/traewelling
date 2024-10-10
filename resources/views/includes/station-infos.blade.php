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
        <div id="collapseOne" class="accordion-collapse collapse"
             aria-labelledby="headingStationMeta"
             data-mdb-parent="#accordionStationMeta">
            <div class="accordion-body p-0">
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

                <div class="mx-4">
                    <p class="fs-5">OpenData</p>
                    <span>Wir versuchen in nächster Zeit mehr Daten von
                        <a href="https://www.wikidata.org/wiki/">Wikidata</a> zu beziehen.
                    </span>
                </div>
                @isset($station->wikidata_id)
                    <div class="mx-4 mb-3">
                        <span>Diese Station ist bereits mit einem
                            <a href="https://www.wikidata.org/wiki/{{ $station->wikidata_id }}" target="_blank">Wikidata-Objekt</a> verknüpft.
                        </span>
                    </div>
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>Bezeichnung</th>
                            <td>
                                @foreach($station->names as $localizedName)
                                    <span class="badge bg-secondary me-1">
                                                            {{$localizedName->language}}: {{$localizedName->name}}
                                                        </span>
                                @endforeach


                                @if($station->names->where('language', app()->getLocale())->count() === 0)
                                    <br/>
                                    <span class="badge bg-danger mt-2">
                                        <i class="fa-solid fa-exclamation-triangle"></i>
                                        Keine Bezeichnung in der aktuellen Sprache ({{app()->getLocale()}}) gefunden.
                                        <br/>
                                        Bitte ergänze die Daten auf Wikidata mit dem
                                        <a href="https://www.wikidata.org/wiki/Property:P2561">Property:P2561</a>.
                                    </span>
                                @endif
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

                    <div class="mx-4 mb-3">
                        <small>
                            Fehler gefunden? Auf Wikidata bearbeiten!
                            Derzeit aktualisieren wir die Daten von Wikidata nur sehr unregelmäßig.
                            Es kann daher lange dauern, bis deine Änderungen hier angezeigt
                            werden.
                        </small>
                        <div>
                            <a href="https://www.wikidata.org/wiki/{{ $station->wikidata_id }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-edit"></i>
                                Bearbeiten
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" title="Neue Daten von Wikidata fetchen und damit die lokalen Daten hier aktualisieren." onclick="fetchWikidata({{$station->id}})">
                                <i class="fa-solid fa-edit"></i>
                                Stationsdaten refreshen
                            </button>
                        </div>
                    </div>

                <script>
                    // TODO: prevent duplicate code with `wikidata/index.blade.php` (by adding a `allowUpdate` parameter)
                    function fetchWikidata(stationId) {
                        console.log('Fetching Wikidata for station ' + stationId);
                        fetch('/api/v1/experimental/station/' + stationId + '/wikidata?allowUpdate=1', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('wikidata result:', data);
                                if (data.error) {
                                    notyf.error(data.error || 'Error fetching Wikidata');
                                } else {
                                    notyf.success(data.message || 'Wikidata fetched');
                                    document.getElementById('station-' + stationId).remove();
                                }
                            })
                            .catch(error => notyf.error(error || 'Error during Wikidata fetch'))
                    }
                </script>
                @else
                    <div class="mx-4 mb-3">
                        <p>
                            Diese Station ist noch nicht mit einem Wikidata-Objekt verknüpft,
                            daher können wir aktuell keine weiteren Informationen anzeigen.
                        </p>
                        <p>
                            Du kannst helfen, indem du die Stationsdaten bei Wikidata pflegt.
                            Wichtig sind insbesondere Identifier wie die IBNR, IFOPT oder das RL-100
                            Kürzel.
                        </p>
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>
