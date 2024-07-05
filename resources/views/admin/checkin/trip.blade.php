@extends('admin.layout')

@section('title', 'Checkin')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <form class="center" method="post" action="{{route('admin.checkin')}}">
                        @csrf
                        <input type="hidden" id="input-tripID" name="tripId" value="{{ $hafasTrip['trip_id']  }}"/>
                        <input type="hidden" name="lineName" value="{{ $hafasTrip['linename'] }}"/>
                        <input type="hidden" id="input-destination" name="destination"
                               value="{{ $hafasTrip['destination'] }}"/>
                        <input type="hidden" name="start" value="{{request()->start}}"/>
                        <input type="hidden" name="ibnr" value="on"/>
                        <input type="hidden" name="departure" value="{{request()->departure}}"/>
                        <input type="hidden" name="userId" value="{{$user->id}}"/>

                        <div class="row mt-1">
                            <div class="col">
                                <div class="form-floating mb-2">
                                    <select class="form-select" name="destinationStopover" required>
                                        <option selected value="">Open this select menu</option>
                                        @php /** @var \App\Models\Stopover $stopover */ @endphp
                                        @foreach($stopovers as $stopover)
                                            @if($stopover->arrival_planned->isBefore(\Carbon\Carbon::parse(request()->departure)))
                                                @continue
                                            @endif

                                            @if($stopover->cancelled)
                                                <option value="{{$stopover->id}}" disabled>
                                                    Arr: {{$stopover->arrival_planned->format('H:i')}}
                                                    Dep: {{$stopover->departure_planned->format('H:i')}}
                                                    //
                                                    {{$stopover->trainStation->name}}
                                                    ({{ __('stationboard.stop-cancelled') }})
                                                </option>
                                            @else
                                                <option value="{{$stopover->id}}">
                                                    Arr: {{$stopover->arrival_planned->format('H:i')}}
                                                    Dep: {{$stopover->departure_planned->format('H:i')}}
                                                    //
                                                    {{$stopover->trainStation->name}}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <label for="destination" class="form-label">Ausstieg</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-floating mb-2">
                                    <textarea maxlength="280" class="form-control" aria-label="Haltestelle"
                                              name="body" id="formBody"></textarea>
                                    <label for="date" class="form-label">Statustext</label>
                                </div>
                                <small>
                                    <a href="javascript:void(0)"
                                       onclick="document.getElementById('formBody').value = 'Ich habe mit der #Träwelling-Hotline eingecheckt!';">
                                        Einfügen: Ich habe mit der #Träwelling-Hotline eingecheckt!
                                    </a>
                                </small>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <select name="business" required class="form-control">
                                        <option value="">bitte wählen</option>
                                        @foreach(\App\Enum\Business::cases() as $case)
                                            <option value="{{$case->value}}">
                                                {{ __('stationboard.business.' . strtolower($case->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="formStation" class="form-label">Reisegrund</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <select name="visibility" required class="form-control" id="formVisibility">
                                        <option value="">bitte wählen</option>
                                        @foreach(\App\Enum\StatusVisibility::cases() as $case)
                                            <option value="{{$case->value}}">
                                                {{ __('status.visibility.' . strtolower($case->value)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="formVisibility" class="form-label">Sichtbarkeit</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mastodon"
                                           name="toot" {{$user->mastodonUrl ? '' : 'disabled'}}>
                                    <label class="form-check-label" for="mastodon">
                                        Mastodon
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="shouldChain_check"
                                           name="chainPost" {{$user->mastodonUrl && \App\Http\Controllers\Backend\Social\MastodonController::getLastSavedPostIdFromUserStatuses($user) ? '' : 'disabled'}}>
                                    <label class="form-check-label" for="shouldChain_check">
                                        Toot Chaining
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 border-top border-bottom">
                            <div class="col">
                                <input type="checkbox" class="form-check-input" id="force" name="force">
                                <label class="form-check-label" for="force">Checkin forcieren? (Punkte werden hierfür
                                    abgezogen)</label>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Einchecken</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
