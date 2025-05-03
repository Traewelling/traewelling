@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5 card-title mb-4">Edit status</h2>
                    <form method="GET" action="{{route('admin.status.edit')}}">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label" for="form-statusId">Status ID?</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" id="form-statusId" class="form-control" name="statusId"/>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Bearbeiten</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5 card-title mb-4" id="h-last-journeys">Last journeys</h2>

                    <hr/>
                    <form method="GET">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="inputUsername" name="userQuery"
                                   value="{{request()->get('userQuery')}}"
                                   placeholder="Username / Displayname"/>
                            <label for="inputUsername" class="form-label">
                                Username / Displayname
                            </label>
                        </div>
                    </form>
                    <hr/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped" aria-labelledby="h-last-journeys">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Visibility / Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lastStatuses as $status)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}">
                                                {{$status->id}}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{route('admin.users.user', ['id' => $status->user->id])}}">
                                                {{'@'.$status->user->username}}
                                            </a>
                                            <br/>
                                            <small>{{$status->user->name}}</small>
                                        </td>
                                        <td>
                                            <strong>{{$status->checkin?->originStopover->station?->name}}</strong>
                                            @isset($status->checkin?->originStopover->station?->rilIdentifier)
                                                <small>
                                                    ({{$status->checkin->originStopover->station->rilIdentifier}})
                                                </small>
                                            @endisset
                                            <br/>
                                            @isset($status?->checkin?->originStopover->station?->ibnr)
                                                <small>IBNR {{$status->checkin->originStopover->station->ibnr}}</small>
                                                <br/>
                                            @endisset

                                            <small>dep {{$status?->checkin?->departure->diffForHumans()}}</small>
                                        </td>
                                        <td>
                                            <strong>{{$status->checkin?->destinationStopover->station?->name}}</strong>
                                            @isset($status->checkin?->destinationStopover->station?->rilIdentifier)
                                                <small>
                                                    ({{$status->checkin->destinationStopover->station->rilIdentifier}})
                                                </small>
                                            @endisset
                                            <br/>
                                            @isset($status?->checkin?->destinationStopover->station?->ibnr)
                                                <small>IBNR {{$status->checkin->destinationStopover->station->ibnr}}</small>
                                                <br/>
                                            @endisset

                                            <small>arr {{$status?->checkin?->arrival->diffForHumans()}}</small>
                                        </td>
                                        <td>
                                            <small>{{__('status.visibility.' . $status->visibility->value)}}</small>
                                            <br/>
                                            <small>{{__('stationboard.business.' . strtolower($status->business->name))}}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{$lastStatuses->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
