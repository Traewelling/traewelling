@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Status bearbeiten</h5>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Benutzer</label>
                        </div>
                        <div class="col-8">
                            {{$status->user->username}}
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
                            <small>(Betreiber: {{$status->trainCheckIn->HafasTrip->operator?->name}})</small>
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
                                    <select id="form-origin" class="form-control" name="origin">
                                        @foreach($status->trainCheckin->HafasTrip->stopoversNew as $stopover)
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
                                    <select id="form-origin" class="form-control" name="destination">
                                        @foreach($status->trainCheckin->HafasTrip->stopoversNew as $stopover)
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

        <div class="col-md-12 mt-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Stopovers (roh)</h5>
                    <pre style="white-space: pre-wrap;">{{$status->trainCheckin->HafasTrip->stopovers}}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
