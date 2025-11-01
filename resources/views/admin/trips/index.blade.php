@php use App\Http\Controllers\Backend\User\ProfilePictureController; @endphp
@php /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Trip> $trips */ @endphp
@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5 card-title mb-4" id="h-last-trips">Last trips</h2>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" aria-labelledby="h-last-trips">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Checkins</th>
                                <th>Source</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Category</th>
                                <th>Number</th>
                                <th>Line</th>
                                <th>Journey Number</th>
                                <th>Operator</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($trips as $trip)
                                <tr>
                                    <td>
                                        <a href="{{route('admin.trips.show', ['id' => $trip->id])}}">
                                            {{$trip->id}}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $trip->checkins_count }}
                                    </td>
                                    <td>
                                        @if ($trip->user)
                                            <a href="{{route('admin.users.show', ['id' => $trip->user->id])}}">
                                                {{$trip->user->username}}
                                            </a>
                                        @else
                                            <span class="text-xs text-muted">{{ $trip->source }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.station', ['id' => $trip->origin_id]) }}">
                                            {{$trip->originStation?->name}}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.station', ['id' => $trip->destination_id]) }}">
                                            {{$trip->destinationStation->name}}
                                        </a>
                                    </td>
                                    <td>{{$trip->category}}</td>
                                    <td>{{$trip->number}}</td>
                                    <td>
                                        <div class="badge text-bg-dark" style="background-color: #{{$trip->route_color}}!important">
                                            {{$trip->linename}}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $trip->journey_number }}
                                    </td>
                                    <td>
                                        {{ $trip->operator?->name }}
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
