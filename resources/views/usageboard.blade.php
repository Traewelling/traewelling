@extends('layouts.app')

@section('title')
    Usage
@endsection

@section('content')
<div class="container">
    <h4 class="mt-4">Usage board</h4>

    <div class="row">
        <div class="col">
            <canvas
                id="statusesByDateCanvas"
                class="date-canvas"
                data-json='{!! json_encode($statusesByDay) !!}'
                data-title="Statuses per Day"
                data-keys='occurs'
                height="300"
                ></canvas>
        </div>
        <div class="col">
            <canvas
                id="userRegistrationCanvas"
                class="date-canvas"
                data-json='{!! json_encode($userRegistrationsByDay) !!}'
                data-title="User Registrations per Day"
                data-keys='occurs'
                height="300"
                ></canvas>
        </div>
        <div class="col">
            <canvas
                id="userRegistrationCanvas"
                class="date-canvas"
                data-json='{!! json_encode($hafasTripsByDay) !!}'
                data-title="HafasTrips,Polylines"
                data-keys='hafas_trips,polylines'
                height="300"
                ></canvas>
        </div>
    </div>
</div>

@endsection