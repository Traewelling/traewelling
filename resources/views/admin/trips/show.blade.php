@php use App\Helpers\CacheKey;use App\Http\Controllers\Backend\User\ProfilePictureController; @endphp
@extends('admin.layout')

@section('title', 'Trip ' . $trip->id)

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
                            <th>Trip ID</th>
                            <td><input class="w-100" type="text" value="{{ $trip->trip_id }}" disabled/></td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>{{ $trip->category }}</td>
                        </tr>
                        <tr>
                            <th>Mode</th>
                            <td>{{ $trip->mode }}</td>
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
                                    <a href="{{route('admin.users.show', ['id' => $trip->user_id])}}">
                                        <small>({{'@'.$trip->user->username}})</small>
                                    </a>
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <th>Last refreshed</th>
                            <td>{{ userTime($trip->last_refreshed?->format('c')) }}</td>
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
                        <tr>
                            <th></th>
                            <td>
                                @if(Cache::has(CacheKey::getReroutePolylineJobKey($trip->id)))
                                    <span class="text-warning fw-bold">
                                        Reroute job is queued or running.
                                    </span>
                                @else
                                    <a class="btn btn-primary btn-sm"
                                       href="{{ route('admin.trips.reroute', ['id' => $trip->id]) }}"
                                    >
                                        Dispatch Reroute Job
                                    </a>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
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
                                                    href="{{route('admin.users.show', ['id' => $checkin->user->id])}}">{{'@'.$checkin->user->username}}</a></small>
                                            <br/>
                                            <a href="{{route('admin.statuses.edit', ['statusId' => $checkin->status->id])}}">
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
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="card-title fs-5">Stopovers</h2>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">TRWL-ID</th>
                                <th scope="col">Ankunft soll / ist</th>
                                <th scope="col">Abfahrt soll / ist</th>
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
                                    <td title="{{$stopover->arrival_planned?->format('Y-m-d')}}">
                                        <span
                                            style="color: #{{ ProfilePictureController::generateBackgroundHash($stopover->arrival_planned->format('ddmm')) }};">
                                            {{userTime($stopover->arrival_planned)}}
                                        </span>
                                        /
                                        <span
                                            style="color: #{{ ProfilePictureController::generateBackgroundHash($stopover->arrival_real?->format('ddmm') ?? '') }};">
                                            {{userTime($stopover->arrival_real)}}
                                        </span>
                                    </td>
                                    <td title="{{$stopover->departure_planned?->format('Y-m-d')}}">
                                        <span
                                            style="color: #{{ ProfilePictureController::generateBackgroundHash($stopover->departure_planned->format('ddmm')) }};">
                                            {{userTime($stopover->departure_planned)}}
                                        </span>
                                        /
                                        <span
                                            style="color: #{{ ProfilePictureController::generateBackgroundHash($stopover->departure_real?->format('ddmm') ?? '') }};">
                                        {{userTime($stopover->departure_real)}}
                                        </span>
                                    </td>
                                </tr>
                                @if(!$loop->last)
                                    <tr class="bg-body-tertiary">
                                        <td colspan="4" class="py-1 text-center small text-body-secondary">
                                            @if($stopover->route_segment_id)
                                                <a href="{{route('admin.routesegment.show', ['id' => $stopover->route_segment_id])}}">
                                                    ✅ Route segment ↓
                                                </a>
                                            @else
                                                ❌ No route segment ↓
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        <div class="alert alert-info">
                            <strong>Info:</strong> The colors of the times are based on the day of the year.
                            This way you can easily see if there is a date change.
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
