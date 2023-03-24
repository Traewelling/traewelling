@php
    use App\Enum\Business;
    use App\Http\Controllers\Backend\Transport\StationController;
    use App\Http\Controllers\Backend\User\ProfilePictureController;
@endphp
<div class="card status mb-3" id="status-{{ $status->id }}"
     data-trwl-status-body="{{ $status->body }}"
     data-date="{{$status->trainCheckin->departure->isoFormat(__('dateformat.with-weekday'))}}"
     data-trwl-business-id="{{ $status->business->value }}"
     data-trwl-visibility="{{ $status->visibility->value }}"
     @if(auth()->check() && auth()->id() === $status->user_id)
         data-trwl-destination-stopover="{{$status->trainCheckin->destination_stopover->id}}"
     data-trwl-alternative-destinations="{{json_encode(StationController::getAlternativeDestinationsForCheckin($status->trainCheckin))}}"
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
                <img src="{{ ProfilePictureController::getUrl($status->user) }}"
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

                        @if($status->business !== Business::PRIVATE)
                            <span class="pl-sm-2">
                                <i class="fa {{$status->business->faIcon()}}"
                                   data-mdb-toggle="tooltip"
                                   data-mdb-placement="top"
                                   title="{{$status->business->title()}}"
                                   aria-hidden="true">
                                </i>
                                <span class="sr-only">{{$status->business->title()}}</span>
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
                        <p class="status-body"><i class="fas fa-quote-right" aria-hidden="true"></i>
                            {!! nl2br(e(preg_replace('~(\R{2})\R+~', '$1', $status->body))) !!}
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
    <div class="card-footer text-muted interaction row align-items-center py-0 me-1">
        <div class="col-1 px-0 d-lg-none"
             id="avatar-small-{{ $status->id }}">
            <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                <img
                        src="{{ ProfilePictureController::getUrl($status->user) }}"
                        class="profile-image" alt="{{__('settings.picture')}}">
            </a>
        </div>
        <div class="col-6 row row-cols-1 my-1 ps-0 ps-lg-1">
            <div class="col">
                <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                    @if(auth()?->user()?->id == $status->user_id)
                        {{__('user.you')}}
                    @else
                        {{ $status->user->username }}
                    @endif
                </a>
            </div>
            <div class="col">
                {{__('dates.-on-')}}
                <a href="{{ url('/status/'.$status->id) }}">
                    {{ $status->created_at->isoFormat(__('time-format')) }}
                </a>
            </div>
        </div>

        <div class="col-4 row ms-auto justify-content-end">
            <div class="col-1 like-text">
             <span
                     class="like {{ $status->likes->where('user_id', auth()->user()->id)->first() === null ? 'far fa-star' : 'fas fa-star'}}"
                     data-trwl-status-id="{{ $status->id }}"></span>
            </div>
            <div class="col-1 like-text">
                <span class="pl-1 @if($status->likes->count() == 0) d-none @endif"
                      id="like-count-{{ $status->id }}">{{ $status->likes->count() }}
                </span>
            </div>
            <div class="col-1 like-text">
                <i class="fas {{$status->visibility->faIcon()}} visibility-icon text-small"
                   aria-hidden="true" title="{{$status->visibility->title()}}"
                   data-mdb-toggle="tooltip"
                   data-mdb-placement="top"></i>
            </div>
            <div class="col-1">
                <div class="dropdown">
                    <button class="btn btn-sm btn-link" type="button" data-mdb-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-vertical" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu">
                        @auth
                            @if(auth()->user()->id === $status->user_id)
                                <li>
                                    <a class="dropdown-item edit" href="#" data-trwl-status-id="{{ $status->id }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                        {{__('edit')}}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item delete" href="#" data-trwl-status-id="{{$status->id}}">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                        {{__('delete')}}
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="#" class="dropdown-item join"
                                       data-trwl-linename="{{$status->trainCheckIn->HafasTrip->linename}}"
                                       data-trwl-stop-name="{{$status->trainCheckIn->destinationStation->name}}"
                                       data-trwl-trip-id="{{$status->trainCheckIn->trip_id}}"
                                       data-trwl-destination="{{$status->trainCheckIn->destination}}"
                                       data-trwl-arrival="{{$status->trainCheckIn->arrival}}"
                                       data-trwl-start="{{$status->trainCheckIn->origin}}"
                                       data-trwl-departure="{{$status->trainCheckIn->departure}}"
                                    >
                                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                                    </a>
                                </li>
                            @endif
                            @admin
                            <li>
                                <hr class="dropdown-divider"/>
                            </li>
                            <li>
                                <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}"
                                   class="dropdown-item">
                                    <i class="fas fa-tools" aria-hidden="true"></i>
                                </a>
                            </li>
                            @endadmin
                        @endauth
                    </ul>
                </div>
            </div>
        </div>

    </div>
        @if(Route::current()->uri == "status/{id}")
            @foreach($status->likes as $like)
                <div class="card-footer text-muted clearfix">
                    <a href="{{ route('profile', ['username' => $like->user->username]) }}">
                        <img src="{{ ProfilePictureController::getUrl($like->user) }}"
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
        @include('includes.check-in-modal')
</div>
