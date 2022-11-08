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
                        <input type="hidden" name="startIBNR" value="{{request()->startIBNR}}"/>
                        <input type="hidden" name="departure" value="{{request()->departure}}"/>
                        <input type="hidden" name="userId" value="{{$user->id}}"/>

                        <div class="row mt-1">
                            <div class="col">
                                <div class="form-floating mb-2">
                                    <select class="form-select" name="destination" required>
                                        <option selected value="">Open this select menu</option>
                                        @foreach($stopovers as $stopover)
                                            @if($stopover->arrival_planned->isBefore(\Carbon\Carbon::parse(request()->departure)))
                                                @continue
                                            @endif

                                            @if($stopover->cancelled)
                                                <tr>
                                                    <td>{{ $stopover->trainStation->name }}</td>
                                                    <td>
                                                        <span
                                                            class="text-danger">{{ __('stationboard.stop-cancelled') }}</span><br/>&nbsp;
                                                    </td>
                                                    <td>{{ $stopover->platform }}</td>
                                                </tr>
                                            @else
                                                <tr class="train-destinationrow"
                                                    data-ibnr="{{$stopover->trainStation->ibnr}}"
                                                    data-stopname="{{$stopover->trainStation->name}}"
                                                    data-arrival="{{$stopover->arrival_planned ?? $stopover->departure_planned}}"
                                                >
                                                    <td>{{ $stopover->trainStation->name }}</td>
                                                    <td>
                                                        {{ __('stationboard.arr') }}
                                                        {{ $stopover->arrival_planned->isoFormat(__('time-format'))}}
                                                        @isset($stopover->arrival_real)
                                                            <small>(<span
                                                                    class="traindelay">+{{ $stopover->arrival_real->diffInMinutes($stopover->arrival_planned) }}</span>)</small>
                                                        @endisset
                                                    </td>
                                                    <td class="text-end">
                                                        {{$stopover->arrival_platform_planned}}
                                                        @if(isset($stopover->arrival_platform_real) && $stopover->arrival_platform_real !== $stopover->arrival_platform_planned)
                                                            <span class="text-danger text-decoration-line-through">
                                                        {{ $stopover->arrival_platform_real }}
                                                    </span>
                                                        @endif
                                                    </td>
                                                </tr>
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
                                    <input class="form-check-input" type="checkbox" id="tweet"
                                           name="tweet" {{$user->twitterUrl ? '' : 'disabled'}}>
                                    <label class="form-check-label" for="tweet">
                                        Twitter
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mastodon"
                                           name="toot" {{$user->mastodonUrl ? '' : 'disabled'}}>
                                    <label class="form-check-label" for="mastodon">
                                        Mastodon
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
        <div class="col-4">
            @include('admin.users.usercard')
        </div>
    </div>
@endsection
