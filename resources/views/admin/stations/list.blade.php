@extends('admin.layout')

@section('title', 'Stations' . (request()->has('query') ? ' - Search for "' . request()->get('query') . '"' : ''))

@section('content')

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET">
                        <input type="text" class="form-control" name="query"
                               placeholder="Search for stations by name, rilIdentifier or IBNR"
                               value="{{request()->get('query')}}"
                        />
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if($stations->count() === 0)
                        <p class="font-weight-bold text-danger">
                            There are no stations matching your search criteria.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>WikiData</th>
                                        <th>RilIdentifier</th>
                                        <th>Name</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stations as $station)
                                        <tr>
                                            <td>{{$station->id}}</td>
                                            <td>
                                                <a href="https://www.wikidata.org/wiki/{{$station->wikidata_id}}"
                                                   target="{{$station->wikidata_id}}">
                                                    {{$station->wikidata_id}}
                                                </a>
                                            </td>
                                            <td>{{$station->rilIdentifier}}</td>
                                            <td>
                                                <a href="{{route('admin.station', $station->id)}}">
                                                    {{$station->name}}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="btn-group float-end">
                                                    <a class="btn btn-sm btn-danger"
                                                       href="javascript:deleteStation({{$station->id}})">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$stations->links()}}

                        <script>
                            // not beautiful, but works ;)

                            function deleteStation(id) {
                                if (confirm('Are you sure you want to delete this station?')) {
                                    fetch('/api/v1/station/' + id, {
                                        method: 'DELETE',
                                    })
                                        .then(response => {
                                            if (response.status === 200) {
                                                window.location.reload();
                                                return;
                                            }
                                            response.json().then(data => {
                                                alert(data.message ?? 'Something went wrong. Please try again later.');
                                            });
                                        });
                                }
                            }

                        </script>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">Create station</h2>

                    <form id="create-station-form">
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="create-station-name" required>
                            <label for="create-station-name" class="form-label">Name</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="create-station-ibnr">
                            <label for="create-station-ibnr" class="form-label">IBNR</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="create-station-rilIdentifier">
                            <label for="create-station-rilIdentifier" class="form-label">RilIdentifier</label>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="create-station-lat" required>
                                    <label for="create-station-lat" class="form-label">Latitude</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="create-station-lon" required>
                                    <label for="create-station-lon" class="form-label">Longitude</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>

                    <script>
                        document.getElementById('create-station-form').addEventListener('submit', function (event) {
                            event.preventDefault();

                            const name          = document.getElementById('create-station-name').value;
                            const ibnr          = document.getElementById('create-station-ibnr').value;
                            const rilIdentifier = document.getElementById('create-station-rilIdentifier').value;
                            const latitude      = document.getElementById('create-station-lat').value;
                            const longitude     = document.getElementById('create-station-lon').value;

                            fetch('/api/v1/station', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    name,
                                    ibnr,
                                    rilIdentifier,
                                    latitude,
                                    longitude,
                                }),
                            }).then(response => {
                                if (response.status === 201) {
                                    window.location.reload();
                                    return;
                                }
                                response.json().then(data => {
                                    alert(data.message ?? 'Something went wrong. Please try again later.');
                                });
                            });
                        });
                    </script>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">Merge stations</h2>

                    <form id="merge-stations-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="merge-stations-station1" required>
                                    <label for="merge-stations-station1" class="form-label">Old Station ID</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="merge-stations-station2" required>
                                    <label for="merge-stations-station2" class="form-label">New Station ID</label>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            The old station will be deleted and all its references will be replaced with the new
                            station.
                        </small>
                        <br/>
                        <button type="submit" class="btn btn-primary">Merge</button>
                    </form>

                    <script>
                        document.getElementById('merge-stations-form').addEventListener('submit', function (event) {
                            event.preventDefault();

                            const station1 = document.getElementById('merge-stations-station1').value;
                            const station2 = document.getElementById('merge-stations-station2').value;

                            fetch('/api/v1/station/' + station1 + '/merge/' + station2, {
                                method: 'PUT',
                            }).then(response => {
                                if (response.status === 200) {
                                    window.location.reload();
                                    return;
                                }
                                response.json().then(data => {
                                    alert(data.message ?? 'Something went wrong. Please try again later.');
                                });
                            });
                        });
                    </script>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">Import from Wikidata</h2>

                    <form method="POST" action="{{route('backend.status.import.wikidata')}}">
                        @csrf
                        <div class="form-floating">
                            <input type="text" class="form-control" id="input-import-wikidata-entity" required
                                   name="qId">
                            <label for="input-import-wikidata-entity" class="form-label">Wikidata ID</label>
                        </div>
                        <br/>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
