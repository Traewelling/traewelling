@php use App\Enum\Business;use App\Models\Status; @endphp
@extends('admin.layout')

@section('title', 'Status: ' . $status->id)

@section('actions')
    @php /** @var Status $status */ @endphp
    <a class="btn btn-secondary float-end" href="{{ route('status', ['id' => $status->id]) }}">
        <i class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
        <span class="d-none d-md-inline">Frontend</span>
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-2">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">User</label>
                        </div>
                        <div class="col-8">
                            {{$status->user->name}}
                            <small>
                                <a href="{{route('admin.users.user', ['id' => $status->user->id])}}">
                                    {{'@'.$status->user->username}}
                                </a>
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4"></div>
                        <div class="col-8">
                            {{ $status->likes->count() }} Likes |
                            {{ $status->checkin->distance / 1000 }} km |
                            {!! durationToSpan(secondsToDuration($status->checkin->duration * 60))  !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Created at</label>
                        </div>
                        <div class="col-8">
                            {{$status->created_at->format('c')}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Updated at</label>
                        </div>
                        <div class="col-8">
                            {{$status->updated_at->format('c')}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Trip</label>
                        </div>
                        <div class="col-8">
                            {{$status->checkin->trip->linename}}
                            @isset($status->checkin->trip->operator?->name)
                                <small>(Operator: {{$status->checkin->trip->operator?->name}})</small>
                            @endisset
                            <br/>
                            <a href="{{route('admin.trip.show', ['id' => $status->checkin->trip->id])}}">
                                {{ $status->checkin->id }} ({{ $status->checkin->trip->source }})
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label>Client</label>
                        </div>
                        <div class="col-8">
                            @isset($status?->client)
                                {{$status->client->name}} (#{{$status->client->id}})
                            @else
                                <span class="text-muted fw-light">No external client (Träwelling 🎉)</span>
                            @endisset
                        </div>
                    </div>

                    <form method="POST" action="{{route('admin.status.edit')}}" class="mt-3">

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
                                        @foreach($status->checkin->trip->stopovers as $stopover)
                                            <option value="{{$stopover->trainStation->id}}"
                                                    @if($stopover->is($status->checkin->originStopover)) selected @endif>
                                                {{$stopover->trainStation->name}}
                                                (A:{{userTime($stopover->arrival, 'H:m')}},
                                                D:{{userTime($stopover->departure, 'H:m')}})
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
                                        @foreach($status->checkin->trip->stopovers as $stopover)
                                            <option value="{{$stopover->trainStation->id}}"
                                                    @if($stopover->is($status->checkin->destinationStopover)) selected @endif>
                                                {{$stopover->trainStation->name}}
                                                (A:{{userTime($stopover->arrival, 'H:m')}},
                                                D:{{userTime($stopover->departure, 'H:m')}})
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
                        <div class="mb-4">
                            <div class="row">
                                <div class="col">
                                    <label class="form-label" for="form-visibility">Visibility</label>
                                    <select id="form-visibility" class="form-control" name="visibility" required>
                                        <option value="">bitte wählen</option>
                                        @foreach(\App\Enum\StatusVisibility::cases() as $case)
                                            <option value="{{$case->value}}"
                                                    @if($status->visibility->value == $case->value) selected @endif>
                                                {{$case->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label" for="form-business">Business</label>
                                    <select id="form-business" class="form-control" name="business" required>
                                        <option value="">bitte wählen</option>
                                        @foreach(Business::cases() as $case)
                                            <option value="{{$case->value}}"
                                                    @if($status->business->value == $case->value) selected @endif>
                                                {{$case->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="row">
                                <div class="col-4">
                                    <label class="form-label" for="form-origin">Event ID</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" name="event_id"
                                           value="{{$status->event_id}}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="row">
                                <div class="col-4">
                                    <label class="form-label" for="form-origin">Points</label>
                                </div>
                                <div class="col-8">
                                    <input type="number" class="form-control" name="points"
                                           value="{{$status->checkin->points}}"/>
                                    <small class="text-muted">
                                        empty for recalculating
                                    </small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-save"></i>
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-2">
                <div class="card-header">
                    Tags
                </div>
                <div class="card-body">
                    @foreach($status->tags as $tag)
                        <span class="badge text-bg-danger">
                             {{ str_replace('tag.title.', '', __('tag.title.' . $tag->key)) }}:
                             <i>{{ $tag->value }}</i>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
