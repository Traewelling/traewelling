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
                <div class="card-header">Details</div>
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
                    <div class="row mb-2">
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
                </div>
            </div>

            <div class="card mb-2">
                <div class="card-header">Moderation</div>
                <div class="card-body">
                    <form method="POST" action="{{route('admin.status.edit')}}">
                        @csrf
                        <input type="hidden" name="statusId" value="{{$status->id}}"/>

                        <div class="row mb-2">
                            <div class="col">
                                <div class="form-floating">
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
                                    <label class="form-label" for="form-origin">Origin</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
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
                                    <label class="form-label" for="form-origin">Destination</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-2">
                            <textarea class="form-control" name="body" maxlength="255"
                                      style="min-height: 100px;">{{$status->body}}</textarea>
                            <label class="form-label" for="form-origin">Body</label>
                        </div>

                        <div class="row mb-2">
                            <div class="col">
                                <div class="form-floating">
                                    <select id="form-visibility" class="form-control" name="visibility" required>
                                        <option value="">bitte wählen</option>
                                        @foreach(\App\Enum\StatusVisibility::cases() as $case)
                                            <option value="{{$case->value}}"
                                                    @if($status->visibility->value == $case->value) selected @endif>
                                                {{$case->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="form-label" for="form-visibility">Visibility</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <select id="form-business" class="form-control" name="business" required>
                                        <option value="">bitte wählen</option>
                                        @foreach(Business::cases() as $case)
                                            <option value="{{$case->value}}"
                                                    @if($status->business->value == $case->value) selected @endif>
                                                {{$case->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="form-label" for="form-business">Business</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control" name="event_id"
                                           value="{{$status->event_id}}"
                                    />
                                    <label class="form-label" for="form-origin">Event ID (empty = none)</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating mb-2">
                                    <input type="number" class="form-control" name="points"
                                           value="{{$status->checkin->points}}"
                                    />
                                    <label class="form-label" for="form-origin">Points (empty for recalculating)</label>
                                </div>
                            </div>
                        </div>

                        <hr/>
                        <h2 class="fs-5 text-warning mb-0">
                            Danger Zone
                        </h2>
                        <small>
                            <i class="fa-solid fa-exclamation-triangle"></i>
                            The note will be visible to the user.
                            Explain why you changed the status.
                        </small>

                        <div class="my-2">
                            <div class="form-floating">
                                <textarea class="form-control" name="moderation_notes" maxlength="255"
                                          style="min-height: 100px;">{{$status->moderation_notes}}</textarea>
                                <label class="form-label" for="form-origin">Moderation Notes</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-floating">
                                    <select class="form-select" name="lock_visibility">
                                        <option value="0" @if($status->lock_visibility == 0) selected @endif>No</option>
                                        <option value="1" @if($status->lock_visibility == 1) selected @endif>Yes</option>
                                    </select>
                                    <label>Lock Visibility?</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <select class="form-select" name="hide_body">
                                        <option value="0" @if($status->hide_body == 0) selected @endif>No</option>
                                        <option value="1" @if($status->hide_body == 1) selected @endif>Yes</option>
                                    </select>
                                    <label>Hide Body from Public?</label>
                                </div>
                            </div>
                        </div>

                        <hr/>

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
