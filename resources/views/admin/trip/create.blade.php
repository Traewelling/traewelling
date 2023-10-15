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
    <div class="alert alert-danger">ToDo: Stopovers and some detailled information</div>

    <form method="POST">
        @csrf

        <div class="form-group">
            <label for="origin">Station Start</label>
            <input type="number" class="form-control" id="origin" name="origin" placeholder="Station Start (IBNR!)"
                   required>
        </div>

        <div class="form-group">
            <label for="departure">Departure (UTC)</label>
            <input type="datetime-local" class="form-control" id="departure" name="departure" placeholder="Departure"
                   required>
        </div>

        <div class="form-group">
            <label for="destination">Station Ziel</label>
            <input type="number" class="form-control" id="destination" name="destination"
                   placeholder="Station Ziel (IBNR!)" required>
        </div>

        <div class="form-group">
            <label for="arrival">Arrival (UTC)</label>
            <input type="datetime-local" class="form-control" id="arrival" name="arrival" placeholder="Arrival"
                   required>
        </div>

        <div class="form-group">
            <label for="operator">Operator</label>
            <select class="form-control" id="operator_id" name="operator_id">
                <option value="">select optionally</option>
                @foreach(\App\Models\HafasOperator::orderBy('name')->get() as $operator)
                    <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="">required</option>
                @foreach(\App\Enum\HafasTravelType::cases() as $travelType)
                    <option>{{ $travelType }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="number">Number</label>
            <input type="text" class="form-control" id="number" name="number"
                   placeholder="something internal - not important, but required" required>
        </div>

        <div class="form-group">
            <label for="linename">Linename</label>
            <input type="text" class="form-control" id="linename" name="linename" placeholder="e.g. ICE 76" required>
        </div>

        <div class="form-group">
            <label for="journey_number">Journey Number</label>
            <input type="text" class="form-control" id="journey_number" name="journey_number" placeholder="e.g. 85014"
                   required>
        </div>

        <button type="submit" class="btn btn-success">
            Create Trip
        </button>
    </form>
@endsection
