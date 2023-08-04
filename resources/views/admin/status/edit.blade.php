@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4 fs-5">Status bearbeiten</h2>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Benutzer</label>
                        </div>
                        <div class="col-8">
                            {{$status->user->name}}
                            <small>
                                <a href="{{route('admin.users.user', ['id' => $status->user->id])}}">
                                    {{'@'.$status->user->username}}
                                </a>
                            </small>
                        </div>

                        <div class="col-4">
                            <label class="form-label" for="form-origin">Eingecheckt am</label>
                        </div>
                        <div class="col-8">
                            {{$status->created_at->format('d.m.Y H:i:s')}}
                        </div>

                        <div class="col-4">
                            <label class="form-label" for="form-origin">Fahrt</label>
                        </div>
                        <div class="col-8">
                            {{$status->trainCheckIn->HafasTrip->linename}}
                            @isset($status->trainCheckIn->HafasTrip->operator?->name)
                                <small>(Betreiber: {{$status->trainCheckIn->HafasTrip->operator?->name}})</small>
                            @endisset
                            <br />
                            <a href="{{route('admin.trip.show', ['id' => $status->trainCheckin->trip_id])}}">
                                {{ $status->trainCheckin->trip_id }}
                            </a>
                        </div>
                    </div>
                    <hr/>
                    <form method="POST" action="{{route('admin.status.edit')}}">
                        @csrf
                        <input type="hidden" name="statusId" value="{{$status->id}}"/>

                        <div class="mb-4">
                            <div class="row">
                                <div class="col-4">
                                    <label class="form-label" for="form-origin">Origin / Abfahrtsort</label>
                                </div>
                                <div class="col-8">
                                    <select id="form-origin" class="form-control" name="origin" required>
                                        <option value="">bitte wählen</option>
                                        @foreach($status->trainCheckin->HafasTrip->stopovers as $stopover)
                                            <option value="{{$stopover->trainStation->id}}"
                                                    @if($stopover->trainStation->ibnr == $status->trainCheckIn->origin) selected @endif>
                                                {{$stopover->trainStation->name}}
                                                (A:{{$stopover->arrival->format('H:i')}},
                                                D:{{$stopover->departure->format('H:i')}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-4">
                                    <label class="form-label" for="form-origin">Destination / Ankunftsort</label>
                                </div>
                                <div class="col-8">
                                    <select id="form-origin" class="form-control" name="destination" required>
                                        <option value="">bitte wählen</option>
                                        @foreach($status->trainCheckin->HafasTrip->stopovers as $stopover)
                                            <option value="{{$stopover->trainStation->id}}"
                                                    @if($stopover->trainStation->ibnr == $status->trainCheckIn->destination) selected @endif>
                                                {{$stopover->trainStation->name}}
                                                (A:{{$stopover->arrival->format('H:i')}},
                                                D:{{$stopover->departure->format('H:i')}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-4">
                                    <label class="form-label" for="form-origin">Status</label>
                                </div>
                                <div class="col-8">
                                    <textarea class="form-control" name="body">{{$status->body}}</textarea>
                                </div>
                            </div>
                        </div>

                        <small class="text-danger">Achtung: Hier sind Admin-Handlungen möglich. Die Änderungen werden
                            nicht auf Plausibilität geprüft!</small>
                        <button type="submit" class="btn btn-primary btn-block">Speichern</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
