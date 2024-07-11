@extends('admin.layout')

@section('title', 'Checkin')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-2">
                <div class="card-body">
                    <h2 class="fs-5 mb-2">Reiseauskunft</h2>

                    <form class="center">
                        <div class="row">
                            <div class="col">
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control"
                                           placeholder="{{__('stationboard.station-placeholder')}} {{__('or-alternative')}} {{__('ril100')}}"
                                           aria-label="{{__('stationboard.station-placeholder')}} {{__('or-alternative')}} {{__('ril100')}}"
                                           name="station" id="formStation"
                                           value="{{isset($station['name']) ? $station['name'] : ''}}">
                                    <label for="formStation" class="form-label">
                                        {{__('stationboard.station-placeholder')}} {{__('or-alternative')}} {{__('ril100')}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="datetime-local" class="form-control"
                                           value="{{($when ?? \Carbon\Carbon::now()->subMinutes(5))->setSecond(0)->toDateTimeLocalString()}}"
                                           name="when" id="formDate">
                                    <label for="formDate" class="form-label">Abfahrtszeit</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <label for="date" class="form-label">Filter</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter1"
                                           value="express">
                                    <label class="form-check-label" for="filter1">FV</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter2"
                                           value="regional">
                                    <label class="form-check-label" for="filter2">Regio</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter3"
                                           value="suburban">
                                    <label class="form-check-label" for="filter3">S</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter4"
                                           value="subway">
                                    <label class="form-check-label" for="filter4">U</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter5"
                                           value="tram">
                                    <label class="form-check-label" for="filter5">Tram</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter6" value="bus">
                                    <label class="form-check-label" for="filter6">Bus</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filter" id="filter7"
                                           value="ferry">
                                    <label class="form-check-label" for="filter7">Fähre</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-floating mb-2">
                                        <input type="text" class="form-control" value="{{$user->username}}"
                                               name="userQuery" id="formUserId">
                                        <label for="formUserId" class="form-label">Benutzername</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col">
                                    <button type="submit" class="btn btn-primary">Suchen</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0 table-responsive">
                    @if(!empty($departures))
                        <table aria-labelledby="stationTableHeader" id="stationboard"
                               class="table table-dark table-borderless table-hover table-striped m-0">
                            <thead>
                                <tr>
                                    <th class="d-sm-table-cell d-lg-none"></th>
                                    <th scope="col" class="ps-2 ps-md-4">
                                        {{__('stationboard.dep-time')}}
                                    </th>
                                    <th scope="col" class="px-0">
                                        {{__('stationboard.line')}}
                                    </th>
                                    <th scope="col">
                                        {{__('stationboard.destination')}}
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departures as $departure)
                                    @if(!$loop->first && !$loop->last && \Carbon\Carbon::parse($departures[$loop->index - 1]->when)->isPast() && \Carbon\Carbon::parse($departures[$loop->index]->when)->isAfter(\Carbon\Carbon::now()->setSecond(0)))
                                        <tr>
                                            <td colspan="4" class="stationboardDivider">
                                                <small>{{__('request-time', ['time' => \Carbon\Carbon::now()->isoFormat(__('time-format'))])}}</small>
                                            </td>
                                        </tr>
                                    @endif

                                    <tr @if(!isset($departure->cancelled)) class="trainrow" @endif>
                                        <td class="d-sm-table-cell d-lg-none">
                                            <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.trip', [
    'tripId' => $departure->tripId,
    'lineName' => $departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr,
    'start' => $departure->stop->id,
    'departure' => $departure->plannedWhen,
    'userId' => $user->id]) }}">
                                                <i class="fas fa-arrow-alt-circle-right" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                        <td class="ps-2 ps-md-4">
                                            @if($departure->delay === null)
                                                <span class="text-white">
                                                        {{userTime($departure->plannedWhen, __('time-format'))}}
                                                    </span>
                                            @elseif($departure->delay === 0)
                                                <span class="text-success">
                                                        {{userTime($departure->plannedWhen, __('time-format'))}}
                                                    </span>
                                            @elseif($departure->delay < (5*60))
                                                <span class="text-warning">
                                                        {{userTime($departure->when, __('time-format'))}}
                                                    </span>
                                                <small class="text-muted text-decoration-line-through">
                                                    {{userTime($departure->plannedWhen, __('time-format'))}}
                                                </small>
                                            @else
                                                <span class="text-danger">
                                                        {{userTime($departure->when, __('time-format'))}}
                                                    </span>
                                                <small class="text-muted text-decoration-line-through">
                                                    {{userTime($departure->plannedWhen, __('time-format'))}}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-nowrap px-0">
                                            @if (file_exists(public_path('img/'.$departure->line->product.'.svg')))
                                                <img alt="{{$departure->line->product}}"
                                                     src="{{ asset('img/'.$departure->line->product.'.svg') }}"
                                                     height="16px">
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
                                                    {{$departure->direction}}
                                                </small>
                                            @else
                                                {{$departure->direction}}
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.trip', [
    'tripId' => rawurlencode($departure->tripId),
    'lineName' => $departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr,
    'start' => $departure->stop->id,
    'departure' => $departure->plannedWhen,
    'userId' => $user->id]) }}">
                                                <i class="fas fa-arrow-alt-circle-right" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @elseif(request()->has('station'))
                        <table class="table table-dark table-borderless m-0">
                            <tr>
                                <td>{{ __('stationboard.no-departures') }}</td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
