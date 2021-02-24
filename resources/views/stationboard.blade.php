@extends('layouts.app')

@section('title')RIS @endsection

@section('content')
    @include('includes.station-autocomplete')
    <div class="container">
        <div id="timepicker-wrapper">
            <div class="row justify-content-center">
                <div class="btn-group" role="group">
                    <a href="{{ route('trains.stationboard', ['provider' => $request->provider, 'station' => $station->name, 'when' => $when->clone()->subMinutes(15)->toIso8601String(), 'travelType' => $request->travelType]) }}"
                       title="{{__('stationboard.minus-15')}}" class="btn btn-light btn-rounded"><i
                                class="fas fa-arrow-circle-left"></i></a>
                    <a href="#" id="timepicker-reveal" title="{{__('stationboard.dt-picker')}}"
                       class="btn btn-light btn-rounded c-datepicker-btn"><i class="fas fa-clock"></i></a>
                    <a href="{{ route('trains.stationboard', ['provider' => $request->provider, 'station' => $station->name, 'when' => $when->clone()->addMinutes(15)->toIso8601String(), 'travelType' => $request->travelType]) }}"
                       title="{{__('stationboard.plus-15')}}" class="btn btn-light btn-rounded"><i
                                class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="row justify-content-center">
                <form class="form-inline opacity-null" id="timepicker-form" method="GET">
                    <input type="hidden" name="provider" value="train"/>
                    <input type="hidden" name="station" value="{{$station->name}}"/>
                    <input type="hidden" name="travelType" value="{{$request->travelType}}"/>
                    <div class="input-group">
                        <input type="datetime-local" class="form-control" id="timepicker" name="when"
                               value="{{  $when->format("Y-m-d\TH:i") }}"/>
                        <div class="input-group-append">
                            <button type="submit" class="input-group-text btn-primary text-white">
                                {{__('stationboard.set-time')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-md-8">

                <div class="card">
                    <div class="card-header">
                        <div class="float-right">
                            <a href="{{ route('user.setHome', ['ibnr' => $station['id']]) }}"><i class="fa fa-home"></i></a>
                        </div>
                        {{ $station['name'] }} <small><i
                                    class="far fa-clock fa-sm"></i>{{ $when->format('H:i (Y-m-d)') }}</small>
                    </div>

                    <div class="card-body p-0 table-responsive">
                        @if(empty($departures))
                            <table class="table table-dark table-borderless m-0">
                                <tr>
                                    <td>{{ __('stationboard.no-departures') }}</td>
                                </tr>
                            </table>
                        @else
                            <table class="table table-dark table-borderless table-hover m-0">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>{{__('stationboard.line')}}</th>
                                        <th>{{__('stationboard.destination')}}</th>
                                        <th>{{__('stationboard.dep-time')}}</th>
                                    </tr>
                                </thead>
                                @foreach($departures as $departure)
                                    <tr @if(!isset($departure->cancelled)) class="trainrow"
                                        @endif data-tripID="{{ $departure->tripId }}"
                                        data-lineName="{{ $departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr }}"
                                        data-start="{{ $departure->stop->id }}"
                                        data-departure="{{ $departure->plannedWhen }}">
                                        <td>@if (file_exists(public_path('img/'.$departure->line->product.'.svg')))
                                                <img class="product-icon"
                                                     alt="Icon of {{$departure->line->product}}"
                                                     src="{{ asset('img/'.$departure->line->product.'.svg') }}">
                                            @else
                                                <i class="fa fa-train"></i>
                                            @endif</td>
                                        <td>
                                            @if($departure->line->name)
                                                {!! str_replace(" ", "&nbsp;", $departure->line->name) !!}
                                            @else
                                                {!! str_replace(" ", "&nbsp;", $departure->line->fahrtNr) !!}
                                            @endif

                                        </td>
                                        <td>{{ $departure->direction }}</td>
                                        <td>
                                            @if(isset($departure->cancelled))
                                                <span class="text-danger">{{ __('stationboard.stop-cancelled') }}</span>
                                            @else
                                                {{\Carbon\Carbon::parse($departure->plannedWhen)->format('H:i')}}
                                                @if(isset($departure->delay))
                                                    <small>(<span
                                                                class="traindelay">+{{ $departure->delay / 60 }}</span>)</small>
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
