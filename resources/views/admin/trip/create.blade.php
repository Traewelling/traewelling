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

    <div id="trip-creation-form">
        <trip-creation-form></trip-creation-form>
    </div>
@endsection
