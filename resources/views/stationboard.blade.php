@extends('layouts.app')
@php(request()->station = $station->name)
@section('content')
    @include('includes.station-autocomplete')
    <div class="container">
    <div id="timepicker-wrapper">
        <div class="row justify-content-center">
            <div class="btn-group" role="group">
                <a href="{{ url()->current() . '?' . http_build_query(['provider' => $request->provider, 'station' => $request->station, 'when' => strtotime('-15 Minutes', $request->when), 'travelType' => $request->travelType]) }}" alt="{{__('stationboard.minus-15')}}" class="btn btn-light btn-rounded"><i class="fas fa-arrow-circle-left"></i></a>
                <a href="#" id="timepicker-reveal" alt="{{__('stationboard.dt-picker')}}" class="btn btn-light btn-rounded c-datepicker-btn"><i class="fas fa-clock"></i></a>
                <a href="{{ url()->current() . '?' . http_build_query(['provider' => $request->provider, 'station' => $request->station, 'when' => strtotime('+15 Minutes', $request->when), 'travelType' => $request->travelType]) }}" alt="{{__('stationboard.plus-15')}}" class="btn btn-light btn-rounded"><i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="form-inline opacity-null" id="timepicker-form">
                <div class="input-group">
                    <input type="datetime-local" class="form-control" id="timepicker"  value="{{  date("Y-m-d\TH:i") }}" />
                    <div class="input-group-append">
                        <a href="#" class="input-group-text btn-primary text-white" id="timepicker-button">{{__('stationboard.set-time')}}</a>
                    </div>
                    <script>
                    window.changeTimeLink = "{{ url()->current() . '?' . http_build_query(['provider' => $request->provider, 'station' => $request->station, 'travelType' => $request->travelType, 'when' => 'REPLACEME' ]) }}";
                    </script>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header">{{ $station->name }} <small><i class="far fa-clock fa-sm"></i> {{ date('H:i (Y-m-d)', $request->when) }}</small></div>

                <div class="card-body p-0">
                    @if(empty($departures))
                        <table class="table table-dark table-borderless table-responsive-lg m-0">
                            <tr>
                                <td>{{ __('stationboard.no-departures') }}</td>
                            </tr>
                        </table>
                    @else
                    <table class="table table-dark table-borderless table-hover table-responsive-lg m-0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{__('stationboard.line')}}</th>
                                <th>{{__('stationboard.destination')}}</th>
                                <th>{{__('stationboard.dep-time')}}</th>
                            </tr>
                        </thead>
                    @foreach($departures as $departure)
                    
                        <tr @if(!isset($departure->cancelled)) class="trainrow" @endif data-tripID="{{ $departure->tripId }}" data-lineName="{{ $departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr }}" data-start="{{ $departure->stop->id }}">
                            <td>@if (file_exists(public_path('img/'.$departure->line->product.'.svg')))
                                    <img class="product-icon" src="{{ asset('img/'.$departure->line->product.'.svg') }}">
                                @else
                                    <i class="fa fa-train"></i>
                                @endif</td>
                            <td>{{ $departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr }}</td>
                            <td>{{ $departure->direction }}</td>
                            <td>
                                @if(isset($departure->cancelled))
                                    <span class="text-danger">{{ __('stationboard.stop-cancelled') }}</span>
                                @else
                                    @if(isset($departure->delay))
                                        {{ date('H:i', strtotime($departure->when) - $departure->delay) }}
                                        <small>(<span class="traindelay">+{{ $departure->delay / 60 }}</span>)</small>
                                    @else
                                    {{ date('H:i', strtotime($departure->when)) }}
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </table>
                        @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
