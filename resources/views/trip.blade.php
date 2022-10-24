@extends('layouts.app')

@section('title', $hafasTrip->linename . ' -> ' . $hafasTrip->destinationStation->name)

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                @isset($searchedStation)
                    <div class="alert alert-warning">
                        {!! __('warning-alternative-station', [
                            'newStation' => $startStation->name,
                            'searchedStation' => $searchedStation->name,
                        ]) !!}
                    </div>
                @endisset

                <div class="card">
                    <div class="card-header"
                         data-linename="{{ $hafasTrip->linename }}"
                         data-startname="{{ $hafasTrip->originStation->name }}"
                         data-start="{{ request()->start }}"
                         data-tripid="{{ $hafasTrip->trip_id }}"
                    >
                        <div class="float-end">
                            <a href="#" class="train-destinationrow"
                               data-ibnr="{{$lastStopover->trainStation->ibnr}}"
                               data-stopname="{{$lastStopover->trainStation->name}}"
                               data-arrival="{{$lastStopover->arrival_planned ?? $lastStopover->departure_planned}}">
                                <i class="fa fa-fast-forward"></i>
                            </a>
                        </div>
                        @if (file_exists(public_path('img/' . $hafasTrip->category->value . '.svg')))
                            <img class="product-icon" src="{{ asset('img/' . $hafasTrip->category->value . '.svg') }}"/>
                        @else
                            <i class="fa fa-train"></i>
                        @endif
                        {{ $hafasTrip->linename }}
                        <i class="fas fa-arrow-alt-circle-right"></i>
                        {{$hafasTrip->destinationStation->name}}
                    </div>

                    <div class="card-body p-0 table-responsive">
                        <table class="table table-dark table-borderless table-hover m-0"
                               data-linename="{{ $hafasTrip->linename }}"
                               data-startname="{{ $hafasTrip->originStation->name }}"
                               data-start="{{ request()->start }}"
                               data-tripid="{{ $hafasTrip->trip_id }}">
                            <thead>
                                <tr>
                                    <th>{{__('stationboard.stopover')}}</th>
                                    <th></th>
                                    <th class="text-end">{{__('platform')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stopovers as $stopover)
                                    @if($stopover->cancelled)
                                        <tr>
                                            <td>{{ $stopover->trainStation->name }}</td>
                                            <td>
                                                <span class="text-danger">{{ __('stationboard.stop-cancelled') }}</span><br/>&nbsp;
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkinModal" tabindex="-1" role="dialog"
         aria-hidden="true" aria-labelledby="checkinModalTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkinModalTitle">{{__('stationboard.new-checkin')}}</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('trains.checkin') }}" method="POST" id="checkinForm">
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">
                                {{__('stationboard.label-message')}}
                            </label>
                            <textarea name="body" class="form-control" id="message-text" maxlength="280"></textarea>
                            <small class="text-muted float-end"><span id="body-length">0</span>/280</small>
                            <script>
                                document.querySelector('#message-text').addEventListener('input', function (e) {
                                    document.querySelector('#body-length').innerText = e.target.value.length;
                                });
                            </script>
                        </div>

                        <div class="mt-2">
                            @if (auth()->user()->socialProfile != null)
                                @if (auth()->user()->socialProfile->twitter_id != null)
                                    <div class="btn-group">
                                        <input type="checkbox" class="btn-check" id="tweet_check" autocomplete="off"
                                               name="tweet_check"/>
                                        <label class="btn btn-sm btn-outline-twitter" for="tweet_check">
                                            <i class="fab fa-twitter"></i>
                                            <span
                                                class="visually-hidden-focusable">{{ __('stationboard.check-tweet') }}</span>
                                        </label>
                                    </div>
                                @endif

                                @if (auth()->user()->socialProfile->mastodon_id != null)
                                    <div class="btn-group">
                                        <input type="checkbox" class="btn-check" id="toot_check" autocomplete="off"
                                               name="toot_check"/>
                                        <label class="btn btn-sm btn-outline-mastodon" for="toot_check">
                                            <i class="fab fa-mastodon"></i>
                                            <span class="visually-hidden-focusable">
                                                {{ __('stationboard.check-toot') }}
                                            </span>
                                        </label>
                                    </div>
                                @endif
                            @endif
                            @include('includes.business-dropdown')
                            @include('includes.visibility-dropdown')
                        </div>

                        @if($events->count() == 1)
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="event_check" name="event"
                                       value="{{ $events[0]->id }}"/>
                                <label class="custom-control-label" for="event_check">
                                    {{ __('events.on-my-way-to', ['name' => $events[0]->name]) }}
                                </label>
                            </div>
                        @elseif($events->count() > 1)
                            <div class="form-group">
                                <label for="event-dropdown" class="col-form-label">
                                    {{__('events.on-my-way-dropdown')}}
                                </label>
                                <select class="form-control" id="event-dropdown" name="event">
                                    <option value="" selected>{{ __('events.no-event-dropdown') }}</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <input type="hidden" id="input-tripID" name="tripID" value=""/>
                        <input type="hidden" id="input-destination" name="destination" value=""/>
                        <input type="hidden" name="start" value="{{request()->start}}"/>
                        <input type="hidden" name="departure" value="{{request()->departure}}"/>
                        <input type="hidden" id="input-arrival" name="arrival"/>
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-mdb-dismiss="modal">
                        {{ __('menu.abort') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="checkinButton">
                        {{ __('stationboard.btn-checkin') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
