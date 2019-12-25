@extends('layouts.app')

@section('title')
    Usage
@endsection

@section('content')
<div class="container-fluid">
    <h5 class="mt-4">Usage board</h5>

    <form method="get" class="row">
        @csrf
        <div class="col-6 col-md-4">
            <label for="begin">{{__('export.begin')}}:</label>
            <input name="begin" type="date" value="{{ $begin }}" class="form-control">
        </div>
        <div class="col-6 col-md-4">
            <label for="end">{{__('export.end')}}:</label>
            <input name="end" type="date" value="{{ $end }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="btn">&nbsp;</label>
            <input type="submit" id="btn btn-primary" value="Bereich auswählen" class="btn-primary form-control">
        </div>
    </form>

    <div class="row pt-4">
        <div class="col">
            <h5 class="text-center pt-3">Eingecheckte Verbindungen</h5>
            <canvas
                id="statusesByDateCanvas"
                class="date-canvas"
                data-labels='{!! json_encode($dates) !!}'
                data-json='{!! json_encode($statusesByDay) !!}'
                data-title="# Statuses"
                data-keys='occurs'
                height="300"
                ></canvas>
        </div>
        <div class="col">
            <h5 class="text-center pt-3">Registrierte Nutzer</h5>
            <canvas
                id="userRegistrationCanvas"
                class="date-canvas"
                data-labels='{!! json_encode($dates) !!}'
                data-json='{!! json_encode($userRegistrationsByDay) !!}'
                data-title="# neue Nutzer"
                data-keys='occurs'
                height="300"
                ></canvas>
        </div>
{{--    </div>--}}
{{--    <div class="row pt-4">--}}
        <div class="col">
            <h5 class="text-center pt-3">Hafas-Einträge nach Transport-Typen</h5>
            <canvas
                id="transportTypesCanvas"
                class="date-canvas"
                data-labels='{!! json_encode($dates) !!}'
                data-json='{!! json_encode($hafasTripsByDay) !!}'
                data-title="Tram,Bus,U-Bahn,S-Bahn,Fähre,Regionalbahn,Regional-Expresss,Schnellzug,Fernverkehr,Fernverkehr (schnell)"
                data-keys='tram,bus,subway,suburban,ferry,regional,regionalExp,express,national,nationalExpress'
                height="300"
            ></canvas>
        </div>
        <div class="col">
            <h5 class="text-center pt-3">Hafas-Einträge und Anzahl der dazugehörigen Polylines</h5>
            <canvas
                id="hafasPolylinesCanvas"
                class="date-canvas"
                data-labels='{!! json_encode($dates) !!}'
                data-json='{!! json_encode($hafasTripsByDay) !!}'
                data-title="HafasTrips,Polylines"
                data-keys='hafas,polylines'
                height="300"
            ></canvas>
        </div>
    </div>
</div>

@endsection
