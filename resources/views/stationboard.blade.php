@extends('layouts.app')

@section('content')
    @include('includes.station-autocomplete')

    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Stationboard</div>

                <div class="card-body">
                    <table id="my-table-id" class="table table-dark table-borderless table-hover table-responsive-lg">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Line</th>
                                <th>Destination</th>
                                <th>Departure</th>
                                <th>Delay</th>
                            </tr>
                        </thead>
                    @foreach($departures as $departure)
                        <tr class="trainrow" data-tripID="{{ $departure->tripId }}" data-lineName="{{ $departure->line->name }}" data-start="{{ $departure->stop->id }}">
                            <td>@if (file_exists(public_path('img/'.$departure->line->product.'.svg')))
                                    <img class="product-icon" src="{{ asset('img/'.$departure->line->product.'.svg') }}">
                                @else
                                    <i class="fa fa-train"></i>
                                @endif</td>
                            <td>{{ $departure->line->name }}</td>
                            <td>{{ $departure->direction }}</td>
                            <td>{{ date('H:i', strtotime($departure->when)) }} Uhr</td>
                            <td>{{ $departure->delay / 60 }}</td>
                        </tr>
                    @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
