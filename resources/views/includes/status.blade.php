@php
    use App\Enum\Business;use App\Http\Controllers\Backend\Transport\StationController;use App\Http\Controllers\Backend\User\ProfilePictureController;
@endphp
<div class="card status mb-3" id="status-{{ $status->id }}"
     data-trwl-id="{{$status->id}}"
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
            class="progress-bar progress-time {{ $status->event?->isPride ? 'progress-pride' : '' }}"
            role="progressbar"
            style="width: 0"
            data-valuenow="{{ time() }}"
            data-valuemin="{{ $status->trainCheckin?->origin_stopover?->departure->timestamp ?? $status->trainCheckin->departure->timestamp }}"
            data-valuemax="{{ $status->trainCheckin?->destination_stopover?->arrival->timestamp ?? $status->trainCheckin->arrival->timestamp }}"
        ></div>
    </div>
    <div class="card-footer text-muted interaction px-3 px-md-4">
        <ul class="list-inline float-end">
            @can('like', $status)
                <li class="like-text list-inline-item me-0">
                    <a href="{{ auth()->user() ? '#' : route('login') }}"
                       class="like {{ auth()->user() && $status->likes->where('user_id', auth()->user()->id)->first() !== null ? 'fas fa-star' : 'far fa-star'}}"
                       data-trwl-status-id="{{ $status->id }}"></a>
                </li>
                <li class="like-text list-inline-item">
                        <span class="likeCount pl-1 @if($status->likes->count() == 0) d-none @endif">
                            {{ $status->likes->count() }}
                        </span>
                </li>
            @endcan
            <li class="like-text list-inline-item">
                <i class="fas {{$status->visibility->faIcon()}} visibility-icon text-small"
                   aria-hidden="true" title="{{$status->visibility->title()}}"
                   data-mdb-toggle="tooltip"
                   data-mdb-placement="top"></i>
            </li>
            <li class="like-text list-inline-item">
                <div class="dropdown">
                    <a href="#" data-mdb-toggle="dropdown" aria-expanded="false">
                        &nbsp;
                        <i class="fa fa-ellipsis-vertical" aria-hidden="true"></i>
                        &nbsp;
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <button class="dropdown-item trwl-share"
                                    type="button"
                                    data-trwl-share-url="{{ route('statuses.get', ['id' => $status->id]) }}"
                                    @if(auth()->user() && $status->user_id == auth()->user()->id)
                                        data-trwl-share-text="{{ $status->socialText }}"
                                    @else
                                        data-trwl-share-text="{{ $status->description }}"
                                @endif
                            >
                                <div class="dropdown-icon-suspense">
                                    <i class="fas fa-share" aria-hidden="true"></i>
                                </div>
                                {{__('menu.share')}}
                            </button>
                        </li>
                        @auth
                            @if(auth()->user()->id === $status->user_id)
                                <li>
                                    <button class="dropdown-item edit" type="button"
                                            data-trwl-status-id="{{ $status->id }}">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </div>
                                        {{__('edit')}}
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item delete" type="button"
                                            data-mdb-toggle="modal"
                                            data-mdb-target="#modal-status-delete"
                                            onclick="document.querySelector('#modal-status-delete input[name=\'statusId\']').value = '{{$status->id}}';">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </div>
                                        {{__('delete')}}
                                    </button>
                                </li>
                            @else
                                <li>
                                    <button type="button" class="dropdown-item join"
                                            data-trwl-linename="{{$status->trainCheckIn->HafasTrip->linename}}"
                                            data-trwl-stop-name="{{$status->trainCheckIn->destinationStation->name}}"
                                            data-trwl-trip-id="{{$status->trainCheckIn->trip_id}}"
                                            data-trwl-destination="{{$status->trainCheckIn->destination}}"
                                            data-trwl-arrival="{{$status->trainCheckIn->arrival}}"
                                            data-trwl-start="{{$status->trainCheckIn->origin}}"
                                            data-trwl-departure="{{$status->trainCheckIn->departure}}"
                                    >
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                                        </div>
                                        {{__('status.join')}}
                                    </button>
                                </li>
                                <x-mute-button :user="$status->user" :dropdown="true"/>
                                <x-block-button :user="$status->user" :dropdown="true"/>
                            @endif
                            @admin
                            <li>
                                <hr class="dropdown-divider"/>
                            </li>
                            <li>
                                <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}"
                                   class="dropdown-item">
                                    <div class="dropdown-icon-suspense">
                                        <i class="fas fa-tools" aria-hidden="true"></i>
                                    </div>
                                    Admin-Interface
                                </a>
                            </li>
                            @endadmin
                        @endauth
                    </ul>
                </div>
            </li>
        </ul>

        <ul class="list-inline">
            <li id="avatar-small-{{ $status->id }}" class="d-lg-none list-inline-item">
                <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                    <img
                        src="{{ ProfilePictureController::getUrl($status->user) }}"
                        class="profile-image" alt="{{__('settings.picture')}}">
                </a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                    @if(auth()?->user()?->id == $status->user_id)
                        {{__('user.you')}}
                    @else
                        {{ $status->user->username }}
                    @endif
                </a>
                {{__('dates.-on-')}}
                <a href="{{ route('statuses.get', ['id' => $status->id]) }}">
                    {{ $status->created_at->isoFormat(__('time-format')) }}
                </a>
            </li>
        </ul>
    </div>
    @if(\Illuminate\Support\Facades\Gate::allows('like', $status) && Route::current()->uri == "status/{id}")
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
</div>
