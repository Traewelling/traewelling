<div class="card status mb-3" id="status-{{ $status->id }}"
     data-trwl-status-body="{{ $status->body }}"
     data-date="{{$status->trainCheckin->departure->isoFormat(__('dateformat.with-weekday'))}}"
     data-trwl-business-id="{{ $status->business->value }}"
     data-trwl-visibility="{{ $status->visibility->value }}"
     @if(auth()->check() && auth()->id() === $status->user_id)
         data-trwl-destination-stopover="{{$status->trainCheckin->destination_stopover->id}}"
     data-trwl-alternative-destinations="{{json_encode(\App\Http\Controllers\Backend\Transport\StationController::getAlternativeDestinationsForCheckin($status->trainCheckin))}}"
    @endif
>
    @if (isset($polyline) && $polyline !== '[]' && Route::current()->uri == "status/{id}")
        <div class="card-img-top">
            <div id="map-{{ $status->id }}" class="map statusMap embed-responsive embed-responsive-16by9"
                 data-polygon="{{ $polyline }}"></div>
        </div>
    @endif

    <div class="card-body row">
        <div class="col-2 image-box pe-0 d-none d-lg-flex">
            <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                <img src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($status->user) }}"
                     alt="{{ $status->user->username }}">
            </a>
        </div>

        <div class="col ps-0">
            <ul class="timeline">
                <li>
                    <i class="trwl-bulletpoint" aria-hidden="true"></i>
                    <span class="text-trwl float-end">
                        @if($status->trainCheckin?->origin_stopover?->isDepartureDelayed)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ $status->trainCheckin->origin_stopover->departure_planned->isoFormat(__('time-format')) }}
                            </small>
                            &nbsp;
                            {{ $status->trainCheckin->origin_stopover->departure_real->isoFormat(__('time-format')) }}
                        @else
                            {{ $status->trainCheckin?->origin_stopover?->departure->isoFormat(__('time-format')) ?? $status->trainCheckin->departure->isoFormat(__('time-format')) }}
                        @endif
                    </span>

                    <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $status->trainCheckin->Origin->ibnr])}}"
                       class="text-trwl clearfix">
                        {{$status->trainCheckin->Origin->name}}
                    </a>

                    <p class="train-status text-muted">
                        <span>
                            @if (file_exists(public_path('img/' . $status->trainCheckin->HafasTrip->category->value . '.svg')))
                                <img class="product-icon"
                                     src="{{ asset('img/' . $status->trainCheckin->HafasTrip->category->value . '.svg') }}"
                                     alt="{{$status->trainCheckin->HafasTrip->category->value}}"
                                />
                            @else
                                <i class="fa fa-train d-inline" aria-hidden="true"></i>
                            @endif
                            {{ $status->trainCheckin->HafasTrip->linename }}
                            @if(isset($status->trainCheckin->HafasTrip->journey_number) && !str_contains($status->trainCheckin->HafasTrip->linename, $status->trainCheckin->HafasTrip->journey_number))
                                <small>({{$status->trainCheckin->HafasTrip->journey_number}})</small>
                            @endif
                        </span>
                        <span class="ps-2">
                            <i class="fa fa-route d-inline" aria-hidden="true"></i>
                            @if($status->trainCheckin->distance < 1000)
                                {{ $status->trainCheckin->distance }}<small>m</small>
                            @else
                                {{round($status->trainCheckin->distance / 1000)}}<small>km</small>
                            @endif
                        </span>
                        <span class="ps-2">
                            <i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>
                            {!! durationToSpan(secondsToDuration($status->trainCheckin->duration * 60)) !!}
                        </span>

                        @if($status->business === \App\Enum\Business::BUSINESS)
                            <span class="pl-sm-2">
                                <i class="fa fa-briefcase" data-mdb-toggle="tooltip" data-mdb-placement="top"
                                   title="{{ __('stationboard.business.business') }}" aria-hidden="true"></i>
                            </span>
                        @endif
                        @if($status->business === \App\Enum\Business::COMMUTE)
                            <span class="pl-sm-2">
                                <i class="fa fa-building" data-mdb-toggle="tooltip" data-mdb-placement="top"
                                   title="{{ __('stationboard.business.commute') }}" aria-hidden="true"></i>
                            </span>
                        @endif
                        @if($status->event != null)
                            <br/>
                            <span class="pl-sm-2">
                                <i class="fa fa-calendar-day" aria-hidden="true"></i>
                                <a href="{{ route('statuses.byEvent', ['eventSlug' => $status->event->slug]) }}">
                                    {{ $status->event->name }}
                                </a>
                            </span>
                        @endif
                    </p>

                    @if(!empty($status->body))
                        <p class="status-body"><i class="fas fa-quote-right" aria-hidden="true"></i> {{ $status->body }}
                        </p>
                    @endif

                    @if($status->trainCheckin->departure->isPast() && $status->trainCheckin->arrival->isFuture())
                        <p class="text-muted font-italic">
                            {{ __('stationboard.next-stop') }}

                            @php
                                $nextStation = \App\Http\Controllers\Backend\Transport\StatusController::getNextStationForStatus($status);
                            @endphp
                            <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $nextStation?->ibnr])}}"
                               class="text-trwl clearfix">
                                {{$nextStation?->name}}
                            </a>
                        </p>
                    @endif
                </li>
                <li>
                    <i class="trwl-bulletpoint" aria-hidden="true"></i>
                    <span class="text-trwl float-end">
                        @if($status->trainCheckin?->destination_stopover?->isArrivalDelayed)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ $status->trainCheckin->destination_stopover->arrival_planned->isoFormat(__('time-format')) }}
                            </small>
                            &nbsp;
                            {{ $status->trainCheckin->destination_stopover->arrival_real->isoFormat(__('time-format')) }}
                        @else
                            {{ $status->trainCheckin?->destination_stopover?->arrival?->isoFormat(__('time-format')) ?? $status->trainCheckin->arrival->isoFormat(__('time-format')) }}
                        @endif
                    </span>
                    <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $status->trainCheckin->Destination->ibnr])}}"
                       class="text-trwl clearfix">
                        {{$status->trainCheckin->Destination->name}}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="progress">
        <div
            class="progress-bar progress-time"
            role="progressbar"
            style="width: 0"
            data-valuenow="{{ time() }}"
            data-valuemin="{{ $status->trainCheckin?->origin_stopover?->departure->timestamp ?? $status->trainCheckin->departure->timestamp }}"
            data-valuemax="{{ $status->trainCheckin?->destination_stopover?->arrival->timestamp ?? $status->trainCheckin->arrival->timestamp }}"
        ></div>
    </div>
    <div class="card-footer text-muted interaction">
        <span class="float-end like-text">
            <i class="fas
{{["fa-globe-americas", "fa-lock-open", "fa-user-friends", "fa-lock", "fa-user-check"][$status->visibility->value]}} visibility-icon text-small"
               aria-hidden="true" title="{{__('status.visibility.'.$status->visibility->value)}}"
               data-mdb-toggle="tooltip"
               data-mdb-placement="top"></i>
            <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                @if(auth()?->user()?->id == $status->user_id)
                    {{__('user.you')}}
                @else
                    {{ $status->user->username }}
                @endif
            </a>{{__('dates.-on-')}}
            <a href="{{ url('/status/'.$status->id) }}">
                {{ $status->created_at->isoFormat(__('time-format')) }}
            </a>
        </span>
        <ul class="list-inline">
            @auth
                <li class="list-inline-item d-lg-none"
                    id="avatar-small-{{ $status->id }}">
                    <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                        <img
                            src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($status->user) }}"
                            class="profile-image" alt="{{__('settings.picture')}}">
                    </a>
                </li>

                <li class="list-inline-item like-text">
                    <span
                        class="like {{ $status->likes->where('user_id', auth()->user()->id)->first() === null ? 'far fa-star' : 'fas fa-star'}}"
                        data-trwl-status-id="{{ $status->id }}"></span>
                    <span class="pl-1 @if($status->likes->count() == 0) d-none @endif"
                          id="like-count-{{ $status->id }}">{{ $status->likes->count() }}</span>
                </li>
                @if(auth()->user()->id == $status->user_id)
                    <li class="list-inline-item like-text">
                        <a href="#" class="edit" data-trwl-status-id="{{ $status->id }}">
                            <i class="fas fa-edit" aria-hidden="true"></i>
                        </a>
                    </li>

                    <li class="list-inline-item like-text">
                        <a href="#" class="delete" data-trwl-status-id="{{ $status->id }}">
                            <i class="fas fa-trash" aria-hidden="true"></i>
                        </a>
                    </li>
                @endif
                @admin
                    <li class="list-inline-item like-text">
                        <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}">
                            <i class="fas fa-tools" aria-hidden="true"></i>
                        </a>
                    </li>
                @endadmin
            @else
                <li class="list-inline-item d-lg-none" id="avatar-small-{{ $status->id }}">
                    <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                        <img
                            src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($status->user) }}"
                            class="profile-image" alt="{{__('settings.picture')}}">
                    </a>
                </li>
            @endauth
        </ul>
    </div>

    @if(Route::current()->uri == "status/{id}")
        @foreach($status->likes as $like)
            <div class="card-footer text-muted clearfix">
                <a href="{{ route('profile', ['username' => $like->user->username]) }}">
                    <img src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($like->user) }}"
                         class="profile-image float-start me-2" alt="{{__('settings.picture')}}">
                </a>
                <span class="like-text pl-2 d-table-cell">
                    <a href="{{ route('profile', ['username' => $like->user->username]) }}">
                        {{$like->user->username}}
                    </a>
                    @if($like->user->is($status->user))
                        {{ __('user.liked-own-status') }}
                    @else
                        {{ __('user.liked-status') }}
                    @endif
                </span>
            </div>
        @endforeach
    @endif
</div>
