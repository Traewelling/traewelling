@extends('admin.layout')

@section('title', 'Trip ' . $trip->trip_id)

@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td><code>{{ $trip->id }}</code></td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>{{ $trip->category }}</td>
                        </tr>
                        <tr>
                            <th>Internal Number</th>
                            <td>{{ $trip->number }}</td>
                        </tr>
                        <tr>
                            <th>Linename</th>
                            <td>{{ $trip->linename }}</td>
                        </tr>
                        <tr>
                            <th>Journey number</th>
                            <td>{{ $trip->journey_number }}</td>
                        </tr>
                        <tr>
                            <th>Operator</th>
                            <td>{{ $trip->operator?->name }}</td>
                        </tr>
                        <tr>
                            <th>Source</th>
                            <td>
                                {{ $trip->source?->name }}
                                @isset($trip->user)
                                    <a href="{{route('admin.users.user', ['id' => $trip->user_id])}}">
                                        <small>({{'@'.$trip->user->username}})</small>
                                    </a>
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <th>Last refreshed</th>
                            <td>{{ $trip->last_refreshed?->format('c') }}</td>
                        </tr>
                        <tr>
                            <th>Polyline</th>
                            <td>
                                @isset($trip->polyline)
                                    <code>{{ $trip->polyline->id }}</code> ({{ $trip->polyline->source }})
                                    | parent:
                                    <code>{{ $trip->polyline->parent_id ?? "NULL" }}</code> {{ $trip->polyline->parent?->source }}
                                @else
                                    <span class="text-danger">No polyline</span>
                                @endisset
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="card-title fs-5">Checkins</h2>
                    @if($trip->checkins->count() === 0)
                        <span class="fw-bold text-danger">No checkins for this trip.</span>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                @foreach($trip->checkins as $checkin)
                                    <tr>
                                        <td>
                                            {{$checkin->user->name}}
                                            <small><a
                                                    href="{{route('admin.users.user', ['id' => $checkin->user->id])}}">{{'@'.$checkin->user->username}}</a></small>
                                            <br/>
                                            <a href="{{route('admin.status.edit', ['statusId' => $checkin->status->id])}}">
                                                #{{ $checkin->status->id }}
                                            </a>
                                        </td>
                                        <td>
                                            {{$checkin->originStopover->station->name}}
                                            <br/>
                                            <small>
                                                dep {{$checkin->originStopover->departure_planned->format('H:i')}}
                                                +{{$checkin->originStopover->departure_planned->diffInMinutes($checkin->originStopover->departure)}}
                                            </small>
                                        </td>
                                        <td>
                                            {{$checkin->destinationStopover->station->name}}
                                            <br/>
                                            <small>
                                                arr {{$checkin->destinationStopover->arrival_planned->format('H:i')}}
                                                +{{$checkin->destinationStopover->arrival_planned->diffInMinutes($checkin->destinationStopover->arrival)}}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title fs-5">Stopovers</h2>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">TRWL-ID</th>
                                <th scope="col">IBNR</th>
                                <th scope="col">RIL100</th>
                                <th scope="col">Ankunft plan</th>
                                <th scope="col">real</th>
                                <th scope="col">Abfahrt plan</th>
                                <th scope="col">real</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trip->stopovers as $stopover)
                                <tr>
                                    <td>
                                        <a href="{{route('admin.station', $stopover->station->id)}}">
                                            {{$stopover->station->name}}
                                        </a>
                                    </td>
                                    <td>{{$stopover->station?->id}}</td>
                                    <td>{{$stopover->station?->ibnr}}</td>
                                    <td>{{$stopover->station?->rilIdentifier}}</td>
                                    <td title="{{$stopover->arrival_planned?->format('c')}}">
                                        {{userTime($stopover->arrival_planned)}}
                                    </td>
                                    <td title="{{$stopover->arrival_real?->format('c')}}">
                                        {{$stopover->arrival_real?->format('H:i')}}
                                    </td>
                                    <td title="{{$stopover->departure_planned?->format('c')}}">
                                        {{userTime($stopover->departure_planned)}}
                                    </td>
                                    <td title="{{$stopover->departure_real?->format('c')}}">
                                        {{userTime($stopover->departure_real)}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection
