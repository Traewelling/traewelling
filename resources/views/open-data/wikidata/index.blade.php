@extends('layouts.app')

@section('title', 'Open Data: Wikidata')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fs-4">
                    Open Data - Wikidata: Missing station information
                </h1>

                @if(app()->getLocale() !== 'en')
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        {{__('page-only-available-in-language', ['language' => __('language.en')])}}
                    </div>
                @endif


                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <b>What is this page for?</b>
                    <br/>
                    <p>
                        This page lists all stations you're träwelled to, that are missing a Wikidata link.
                        Click the "Fetch" button to fetch the Wikidata information for a station.
                        <br/>
                        If no Wikidata object was found, please help us to assign it by maintaining the data at
                        Wikidata.
                        We will search for the station using the IBNR known to us.
                        <br/>
                        If the station already exists in Wikidata, please add it to the object and "Fetch" again.
                        If not, please create an object.
                    </p>

                    <hr/>
                    <i class="fa-solid fa-file-circle-question"></i>
                    <b>What data is relevant for Träwelling?</b>

                    <p>
                        Träwelling uses the Wikidata object to enrich the station information.
                        The following properties are relevant for Träwelling:
                    </p>
                    <ul>
                        <li>
                            <a href="https://www.wikidata.org/wiki/Property:P954" target="P954">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                IBNR
                            </a>
                        </li>
                        <li>
                            <a href="https://www.wikidata.org/wiki/Property:P12393" target="P12393">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                IFOPT
                            </a>
                        </li>
                        <li>
                            <a href="https://www.wikidata.org/wiki/Property:P8671" target="P8671">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                Ril 100 (DB-Betriebsstellenabkürzung)
                            </a>
                        </li>
                        <li>
                            <a href="https://www.wikidata.org/wiki/Property:P1448" target="P1448">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                Official name
                            </a>
                        </li>
                    </ul>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>IBNR</th>
                            <th>IFOPT</th>
                            <th>Ril100</th>
                            <th>Wikidata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($destinationStationsWithoutWikidata as $station)
                            <tr id="station-{{$station->id}}">
                                <td>{{$station->name}}</td>
                                <td>{{$station->ibnr}}</td>
                                <td>{{$station->ifopt}}</td>
                                <td>{{$station->rilIdentifier}}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="fetchWikidata({{$station->id}})">
                                        <i class="fas fa-link"></i>
                                        Fetch
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <script>
                    function fetchWikidata(stationId) {
                        console.log('Fetching Wikidata for station ' + stationId);
                        fetch('/api/v1/experimental/station/' + stationId + '/wikidata', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                if (data.error) {
                                    notyf.error(data.error || 'Error fetching Wikidata');
                                } else {
                                    notyf.success(data.message || 'Wikidata fetched');
                                    document.getElementById('station-' + stationId).remove();
                                }
                            })
                    }
                </script>
            </div>
        </div>
    </div>
@endsection
