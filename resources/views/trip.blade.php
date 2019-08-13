@extends('layouts.app')

@section('content')
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Train</div>

                <div class="card-body">
                    <table id="my-table-id" class="table table-dark table-borderless table-hover table-responsive-lg">
                        <thead>
                            <tr>
                                <th>Stop</th>
                                <th>Arrival</th>
                                <th>Departure</th>
                                <th>Plattform</th>
                            </tr>
                        </thead>
                        @foreach($stopovers as $stop)
                            <tr data-ibnr="{{$stop['stop']['id']}}">
                                <td>{{ $stop['stop']['name'] }}</td>
                                <td>{{ date('H:i', strtotime($stop['arrival'])) }} <small>{{ $stop['arrivalDelay'] }}</small></td>
                                <td>{{ date('H:i', strtotime($stop['departure'])) }} <small>{{ $stop['departureDelay'] }}</small></td>
                                <td>{{ $stop['departurePlatform'] }}</td>
                            </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
