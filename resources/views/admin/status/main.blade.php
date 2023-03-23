@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5 card-title mb-4">Status bearbeiten</h2>
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
                    <h2 class="fs-5 card-title mb-4">Letzte Reisen</h2>

                    <hr/>
                    <form method="GET">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="inputUsername" name="userQuery"
                                   value="{{request()->get('userQuery')}}"
                                   placeholder="Username / Displayname / SupportCode"/>
                            <label for="inputUsername" class="form-label">
                                Username / Displayname / SupportCode
                            </label>
                        </div>
                    </form>
                    <hr/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Abfahrt</th>
                                    <th>Ankunft</th>
                                    <th>Eingecheckt</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lastStatuses as $status)
                                    <tr>
                                        <td>{{$status->id}}</td>
                                        <td>
                                            <a href="{{route('admin.users.user', ['id' => $status->user->id])}}">
                                                {{'@'.$status->user->username}}
                                            </a>
                                            <br/>
                                            <small>{{$status->user->name}}</small>
                                        </td>
                                        <td>
                                            <strong>{{$status->trainCheckin?->originStation?->name}}</strong>
                                            @isset($status->trainCheckin?->originStation?->rilIdentifier)
                                                <small>
                                                    ({{$status->trainCheckin->originStation->rilIdentifier}})
                                                </small>
                                            @endisset
                                            <br/>
                                            @isset($status?->trainCheckin?->originStation?->ibnr)
                                                <small>IBNR {{$status->trainCheckin->originStation->ibnr}}</small>
                                                <br/>
                                            @endisset

                                            <small>Ankunft {{$status?->trainCheckin?->arrival->diffForHumans()}}</small>
                                        </td>
                                        <td>
                                            <strong>{{$status->trainCheckin?->destinationStation?->name}}</strong>
                                            @isset($status->trainCheckin?->destinationStation?->rilIdentifier)
                                                <small>
                                                    ({{$status->trainCheckin->destinationStation->rilIdentifier}})
                                                </small>
                                            @endisset
                                            <br/>
                                            @isset($status?->trainCheckin?->destinationStation?->ibnr)
                                                <small>IBNR {{$status->trainCheckin->destinationStation->ibnr}}</small>
                                                <br/>
                                            @endisset

                                            <small>Ankunft {{$status?->trainCheckin?->arrival->diffForHumans()}}</small>
                                        </td>
                                        <td>{{$status->created_at->diffForHumans()}}</td>
                                        <td class="text-end">
                                            <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}"
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
