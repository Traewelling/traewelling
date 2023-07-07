@extends('layouts.app')

@section('title', 'RIS')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @include('includes.station-autocomplete')

                <div id="timepicker-wrapper">
                    <div class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('trains.stationboard', ['provider' => request()->provider, 'ibnr' => $station->ibnr, 'when' => $times['prev']->toIso8601String(), 'travelType' => request()->travelType]) }}"
                               title="{{__('stationboard.minus-15')}}"
                               class="btn btn-light">
                                <i class="fas fa-arrow-circle-left"></i>
                            </a>
                            <a href="#" id="timepicker-reveal" title="{{__('stationboard.dt-picker')}}"
                               class="btn btn-light btn-rounded c-datepicker-btn">
                                <i class="fas fa-clock"></i>
                            </a>
                            <a href="{{ route('trains.stationboard', ['provider' => request()->provider, 'ibnr' => $station->ibnr, 'when' => $times['next']->toIso8601String(), 'travelType' => request()->travelType]) }}"
                               title="{{__('stationboard.plus-15')}}"
                               class="btn btn-light">
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <form class="form-inline opacity-null" id="timepicker-form">
                            <input type="hidden" name="ibnr" value="{{$station->ibnr}}"/>
                            <input type="hidden" name="travelType" value="{{request()->travelType}}"/>
                            <div class="input-group mb-3 mx-auto">
                                <input type="datetime-local" class="form-control" id="timepicker" name="when"
                                       aria-describedby="button-addontime"
                                       value="{{ userTime($times['now'], 'Y-m-d\TH:i') }}"/>
                                <button class="btn btn-outline-primary" type="submit" id="button-addontime"
                                        data-mdb-ripple-color="dark">
                                    {{__('stationboard.set-time')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if (
                    !empty($departures) &&
                    \Carbon\Carbon::parse($departures[0]->when)->tz->toOffsetName()
                    !== \Carbon\CarbonTimeZone::create(auth()->user()->timezone)->toOffsetName()
                )
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {!! __("stationboard.timezone", ['timezone' => auth()->user()->timezone]) !!}
                        <p>{!! __("stationboard.timezone.settings", ['url' => route('settings')]) !!}</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="float-end">
                            <a href="{{ route('user.setHome', ['stationName' => $station->name]) }}">
                                <i class="fa fa-home"></i>
                            </a>
                        </div>
                        {{ $station->name }}
                        <small>
                            <i class="far fa-clock fa-sm"></i>
                            {{ userTime($times['now'], __('time-format.with-day')) }}
                        </small>
                    </div>

                    <div class="card-body p-0 table-responsive">
                        @if(empty($departures))
                            <table class="table table-dark table-borderless m-0">
                                <tr>
                                    <td>{{ __('stationboard.no-departures') }}</td>
                                </tr>
                            </table>
                        @else
                            <table aria-labelledby="stationTableHeader" id="stationboard"
                                   class="table table-dark table-borderless table-hover table-striped m-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="ps-2 ps-md-4">
                                            {{__('stationboard.dep-time')}}
                                        </th>
                                        <th scope="col" class="px-0">
                                            {{__('stationboard.line')}}
                                        </th>
                                        <th scope="col">
                                            {{__('stationboard.destination')}}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departures as $departure)
                                        @if(!$loop->first && !$loop->last && \Carbon\Carbon::parse($departures[$loop->index - 1]->when)->isPast() && \Carbon\Carbon::parse($departures[$loop->index]->when)->isAfter(\Carbon\Carbon::now()->setSecond(0)))
                                            <tr>
                                                <td colspan="3" class="stationboardDivider">
                                                    <small>{{__('request-time', ['time' => userTime()])}}</small>
                                                </td>
                                            </tr>
                                        @endif

                                        <tr data-tripID="{{ $departure->tripId }}"
                                            data-lineName="{{ $departure->line->name ?? $departure->line->fahrtNr }}"
                                            data-start="{{ $departure->stop->id }}"
                                            data-departure="{{ $departure->plannedWhen }}"
                                            @if($departure->station->id !== $station->id && $departure->station->name !== $station->name)
                                                data-searched-station="{{$station->id}}"
                                            @endif
                                            @if(!isset($departure->cancelled)) class="trainrow" @endif
                                        >
                                            <td class="ps-2 ps-md-4">
                                                @if($departure->delay === null)
                                                    <span class="text-white">
                                                        {{userTime($departure->plannedWhen)}}
                                                    </span>
                                                @elseif($departure->delay === 0)
                                                    <span class="text-success">
                                                        {{userTime($departure->plannedWhen)}}
                                                    </span>
                                                @elseif($departure->delay < (5*60))
                                                    <span class="text-warning">
                                                        {{userTime($departure->when)}}
                                                    </span>
                                                    <small class="text-muted text-decoration-line-through">
                                                        {{userTime($departure->plannedWhen)}}
                                                    </small>
                                                @else
                                                    <span class="text-danger">
                                                        {{userTime($departure->when)}}
                                                    </span>
                                                    <small class="text-muted text-decoration-line-through">
                                                        {{userTime($departure->plannedWhen)}}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-nowrap px-0">
                                                @if (file_exists(public_path('img/'.$departure->line->product.'.svg')))
                                                    <img alt="{{$departure->line->product}}"
                                                         src="{{ asset('img/'.$departure->line->product.'.svg') }}"
                                                         class="product-icon">
                                                @else
                                                    <i class="fa fa-train"></i>
                                                @endif
                                                &nbsp;
                                                @if($departure->line->name)
                                                    {!! str_replace(" ", "&nbsp;", $departure->line->name) !!}
                                                @else
                                                    {!! str_replace(" ", "&nbsp;", $departure->line->fahrtNr) !!}
                                                @endif
                                            </td>
                                            <td class="text-wrap">
                                                @if(isset($departure->cancelled))
                                                    <span class="text-danger">
                                                        {{ __('stationboard.stop-cancelled') }}
                                                    </span>
                                                    <br/>
                                                    <small class="text-muted text-decoration-line-through">
                                                        {{__('stationboard.to')}}
                                                        {{$departure->direction}}
                                                    </small>
                                                @else
                                                    {{$departure->direction}}
                                                @endif

                                                @if($departure->station->id !== $station->id && $departure->station->name !== $station->name)
                                                    <br/>
                                                    <small class="text-muted">
                                                        {{__('stationboard.dep')}} {{$departure->station->name}}
                                                    </small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
