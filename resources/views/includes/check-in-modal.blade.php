@php use App\Http\Controllers\Backend\EventController; @endphp
@auth
    @php($events = EventController::activeEvents())
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
                        <div class="form-outline">
                        <textarea name="body" class="form-control" id="message-text" maxlength="280"
                                  style="min-height: 130px;"></textarea>
                            <label for="message-text" class="form-label">{{__('stationboard.label-message')}}</label>
                        </div>
                        <small class="text-muted float-end"><span id="message-length">0</span>/280</small>
                        <script>
                            document.querySelector('#message-text').addEventListener('input', function (e) {
                                document.querySelector('#message-length').innerText = e.target.value.length;
                            });
                        </script>

                        <div class="mt-2">
                            @if (auth()->user()?->socialProfile != null)
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
                                    @if (\App\Http\Controllers\Backend\Social\MastodonController::getLastSavedPostIdFromUserStatuses(auth()->user()))
                                        <div class="btn-group">
                                            <input type="checkbox" class="btn-check" id="chainPost_check"
                                                   autocomplete="off"
                                                   name="chainPost_check"/>
                                            <label class="btn btn-sm btn-outline-mastodon" for="chainPost_check">
                                                <i class="fa-solid fa-list-ol"></i>
                                                <span class="visually-hidden-focusable">
                                                    {{ __('stationboard.check-chainPost') }}
                                                </span>
                                            </label>
                                        </div>
                                    @endif
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
                        <input type="hidden" id="input-start" name="start" value="{{request()->start}}"/>
                        <input type="hidden" id="input-departure" name="departure" value="{{request()->departure}}"/>
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
@endauth

