@extends('admin.layout')

@section('title', 'Create new trip manually')

@section('content')

    <div class="alert alert-info">
        This form is currently for testing purposes only.
        Admins can create a trip with manually entered data.
        Users can check in to this trip.
        It should be tested if the trip is created correctly and all data required for the trip is present, so no (500)
        errors occur.
    </div>
    <div class="alert alert-danger">ToDo: Stopovers and some detailed information</div>

    <form method="POST" action="/api/v1/trains/trip">
        @csrf

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="originId">Origin Station (IBNR!)</label>
                    <input type="number" class="form-control" id="originId" name="originId"
                           placeholder="Origin Station (IBNR!)"
                           required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="departure">Departure at Origin (UTC!)</label>
                    <input type="datetime-local" class="form-control" id="originDeparturePlanned"
                           name="originDeparturePlanned" placeholder="Departure at Origin (UTC!)"
                           required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="destinationId">Destination Station (IBNR!)</label>
                    <input type="number" class="form-control" id="destinationId" name="destinationId"
                           placeholder="Destination Station (IBNR!)"
                           required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="destinationArrivalPlanned">Arrival at Destination (UTC!)</label>
                    <input type="datetime-local" class="form-control" id="destinationArrivalPlanned" name="destinationArrivalPlanned"
                           placeholder="Departure at Origin (UTC!)"
                           required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="operatorId">Operator</label>
                    <select class="form-control" id="operatorId" name="operatorId">
                        <option value="">select optionally</option>
                        @foreach(\App\Models\HafasOperator::orderBy('name')->get() as $operator)
                            <option value="{{ $operator->id }}">
                                {{ $operator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select class="form-control" id="category" name="category" required>
                        <option value="">required</option>
                        @foreach(\App\Enum\HafasTravelType::cases() as $travelType)
                            <option>{{ $travelType }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="journeyNumber">Journey Number</label>
                    <input type="text" class="form-control" id="journeyNumber" name="journeyNumber"
                           placeholder="e.g. 85014"
                           required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="lineName">LineName</label>
                    <input type="text" class="form-control" id="lineName" name="lineName" placeholder="e.g. ICE 76"
                           required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            Create Trip
        </button>
    </form>
@endsection
