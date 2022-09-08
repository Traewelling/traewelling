@extends('admin.layout')

@section('title', isset($location) ? 'Edit location: ' . $location->name : 'Create location')

@section('content')
    <form method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="inputName" name="name" placeholder="Name"
                                   required value="{{old('name') ?? ($location ?? null)?->name}}">
                            <label for="inputName">Name</label>
                        </div>

                        <hr/>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="inputStreet" name="address_street"
                                   placeholder="Street" required
                                   value="{{old('address_street') ?? ($location ?? null)?->address_street}}">
                            <label for="inputStreet">Street</label>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="inputZip" name="address_zip"
                                           placeholder="ZIP" required
                                           value="{{old('address_zip') ?? ($location ?? null)?->address_zip}}">
                                    <label for="inputZip">ZIP</label>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="inputCity" name="address_city"
                                           placeholder="City" required
                                           value="{{old('address_city') ?? ($location ?? null)?->address_city}}">
                                    <label for="inputCity">City</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="inputLatitude" name="latitude"
                                           placeholder="Latitude" required
                                           value="{{old('latitude') ?? ($location ?? null)?->latitude}}">
                                    <label for="inputLatitude">Latitude</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="inputLongitude" name="longitude"
                                           placeholder="Longitude" required
                                           value="{{old('longitude') ?? ($location ?? null)?->longitude}}">
                                    <label for="inputLongitude">Longitude</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div id="map" style="min-height: 400px;" class="mb-3"></div>
                <script>
                    var map = L.map('map').setView([51, 10], 5);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        maxZoom: 19,
                    }).addTo(map);

                    L.tileLayer('http://{s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png', {
                        attribution: '<a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>, Style: <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA 2.0</a> <a href="http://www.openrailwaymap.org/">OpenRailwayMap</a> and OpenStreetMap',
                        minZoom: 2,
                        maxZoom: 19,
                        tileSize: 256
                    }).addTo(map);

                    let marker = L.marker([
                        {{($location ?? null)?->latitude ?? 51}},
                        {{($location ?? null)?->longitude ?? 10}},
                    ], {draggable: true})
                        .addTo(map)
                        .on('dragend', function () {
                            let latlng                                      = marker.getLatLng();
                            document.getElementById('inputLatitude').value  = latlng.lat;
                            document.getElementById('inputLongitude').value = latlng.lng;
                        });

                    document.getElementById('inputLatitude').addEventListener('change', function () {
                        marker.setLatLng([this.value, document.getElementById('inputLongitude').value]);
                    });
                    document.getElementById('inputLongitude').addEventListener('change', function () {
                        marker.setLatLng([document.getElementById('inputLatitude').value, this.value]);
                    });
                </script>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-regular fa-floppy-disk" aria-hidden="true"></i>
                            Speichern
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
