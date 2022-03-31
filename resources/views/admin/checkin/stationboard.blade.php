@extends('admin.layout')

@section('title', 'Checkin')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Reiseauskunft</h5>

                    <form class="center">
                        <div class="row">
                            <div class="col">
                                <input type="text" class="form-control" placeholder="Haltestelle / DS100"
                                       aria-label="Haltestelle" name="station"
                                       value="{{isset($station['name']) ? $station['name'] : ''}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="date" class="form-label">Wann</label>
                                <input type="datetime-local" class="form-control"
                                       value="{{($when ?? \Carbon\Carbon::now())->toDateTimeLocalString()}}"
                                       name="when" id="date">
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
                                    <label for="userId" class="form-label">User</label>
                                    <input type="text" class="form-control" value="{{$user->username}}" name="userQuery"
                                           id="user">
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col">
                                    <input type="submit" class="btn btn-primary">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body p-0 table-responsive">
                    @if(!empty($departures))
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
                                        <td class="ps-2 ps-md-4">
                                            @if($departure->delay === null)
                                                <span class="text-white">
                                                        {{\Carbon\Carbon::parse($departure->plannedWhen)->isoFormat(__('time-format'))}}
                                                    </span>
                                            @elseif($departure->delay === 0)
                                                <span class="text-success">
                                                        {{\Carbon\Carbon::parse($departure->plannedWhen)->isoFormat(__('time-format'))}}
                                                    </span>
                                            @elseif($departure->delay < (5*60))
                                                <span class="text-warning">
                                                        {{\Carbon\Carbon::parse($departure->when)->isoFormat(__('time-format'))}}
                                                    </span>
                                                <small class="text-muted text-decoration-line-through">
                                                    {{\Carbon\Carbon::parse($departure->plannedWhen)->isoFormat(__('time-format'))}}
                                                </small>
                                            @else
                                                <span class="text-danger">
                                                        {{\Carbon\Carbon::parse($departure->when)->isoFormat(__('time-format'))}}
                                                    </span>
                                                <small class="text-muted text-decoration-line-through">
                                                    {{\Carbon\Carbon::parse($departure->plannedWhen)->isoFormat(__('time-format'))}}
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
                                            <a class="btn btn-outline-primary" href="{{ route('admin.trip', [
    'tripId' => $departure->tripId,
    'lineName' => $departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr,
    'startIBNR' => $departure->stop->id,
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
        <div class="col">
            @include('admin.users.usercard')
        </div>
    </div>
@endsection
