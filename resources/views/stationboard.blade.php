@extends('layouts.app')
@php(request()->station = $station->name)
@section('content')
    @include('includes.station-autocomplete')
    <div class="container">
    <div class="row justify-content-center">
        <div class="btn-group" role="group" aria-label="Basic example">
            <a href="{{ url()->current() . '?' . http_build_query(['provider' => $request->provider, 'station' => $request->station, 'when' => strtotime('-15 Minutes', $request->when)]) }}" alt="-15 Minutes" class="btn btn-outline-dark"><i class="fas fa-arrow-circle-left"></i></a>
            <a href="{{ url()->current() . '?' . http_build_query(['provider' => $request->provider, 'station' => $request->station, 'when' => strtotime('+15 Minutes', $request->when)]) }}" alt="+15 Minutes" class="btn btn-outline-dark"><i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header">{{ $station->name }} <small><i class="far fa-clock fa-sm"></i> {{ date('H:i (Y-m-d)', $request->when) }}</small></div>

                <div class="card-body p-0">

                    <table id="my-table-id" class="table table-dark table-borderless table-hover table-responsive-lg m-0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Line</th>
                                <th>Destination</th>
                                <th>Departure</th>
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
                            <td>{{ date('H:i', strtotime($departure->when)) }} Uhr @if(isset($departure->delay))<small>+{{ $departure->delay / 60 }}</small>@endif</td>
                        </tr>
                    @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
