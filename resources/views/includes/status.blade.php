<div class="card status mt-3" id="status-{{ $status->id }}" data-trwl-status-body="{{ $status->body }}"
     data-date="{{$status->trainCheckin->departure->isoFormat('dddd, DD. MMMM YYYY')}}"
     data-trwl-business-id="{{ $status->business }}"
>
    @if (Route::current()->uri == "status/{id}")
        @if($status->trainCheckin->HafasTrip->polyline)
            <div class="card-img-top">
                <div id="map-{{ $status->id }}" class="map statusMap embed-responsive embed-responsive-16by9"
                     data-polygon="{{ $status->trainCheckin->getMapLines() }}"></div>
            </div>
        @endif
    @endif

    <div class="card-body row">
        <div class="col-2 image-box pe-0 d-none d-lg-flex">
            <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}">
            </a>
        </div>

        <div class="col ps-0">
            <ul class="timeline">
                <li>
                    <i>&nbsp;</i>
                    <span class="text-trwl float-end">
                        @if($status->trainCheckin?->origin_stopover?->isDepartureDelayed)
                            <small style="text-decoration: line-through;"
                                   class="text-muted">{{ $status->trainCheckin->origin_stopover->departure_planned->format('H:i') }}</small>
                            &nbsp;
                            {{ $status->trainCheckin->origin_stopover->departure_real->format('H:i') }}
                        @else
                            {{ $status->trainCheckin?->origin_stopover?->departure->format('H:i') ?? $status->trainCheckin->departure->format('H:i') }}
                        @endif
                    </span>
                    {!! stationLink($status->trainCheckin->Origin->name) !!}
                    <p class="train-status text-muted">
                        <span>
                            @if (file_exists(public_path('img/'.$status->trainCheckin->HafasTrip->category.'.svg')))
                                <img class="product-icon"
                                     src="{{ asset('img/'.$status->trainCheckin->HafasTrip->category.'.svg') }}">
                            @else
                                <i class="fa fa-train d-inline"></i>
                            @endif {{ $status->trainCheckin->HafasTrip->linename }}
                        </span>
                        <span class="ps-2">
                            <i class="fa fa-route d-inline"></i>&nbsp;
                            {{number($status->trainCheckin->distance, 0)}}<small>km</small>
                        </span>
                        <span class="ps-2">
                            <i class="fa fa-stopwatch d-inline"></i>&nbsp;
                            {!! durationToSpan(secondsToDuration($status->trainCheckin->duration * 60)) !!}
                        </span>

                        @if($status->business == 1)
                            <span class="pl-sm-2">
                                <i class="fa fa-briefcase" data-mdb-toggle="tooltip" data-mdb-placement="top"
                                   title="{{ __('stationboard.business.business') }}"></i>
                            </span>
                        @endif
                        @if($status->business == 2)
                            <span class="pl-sm-2">
                                <i class="fa fa-building" data-mdb-toggle="tooltip" data-mdb-placement="top"
                                   title="{{ __('stationboard.business.commute') }}"></i>
                            </span>
                        @endif
                        @if($status->event != null)
                            <br/>
                            <span class="pl-sm-2">
                                <i class="fa fa-calendar-day"></i>
                                <a href="{{ route('statuses.byEvent', ['eventSlug' => $status->event->slug]) }}">
                                    {{ $status->event->name }}
                                </a>
                            </span>
                        @endif
                    </p>

                    @if(!empty($status->body))
                        <p class="status-body"><i class="fas fa-quote-right"></i> {{ $status->body }}</p>
                    @endif

                    @if($status->trainCheckin->departure->isPast() && $status->trainCheckin->arrival->isFuture())
                        <p class="text-muted font-italic">
                            {{ __('stationboard.next-stop') }}:
                            {!! stationLink(\App\Http\Controllers\FrontendStatusController::nextStation($status)) !!}
                        </p>
                    @endif
                </li>
                <li>
                    <i>&nbsp;</i>
                    <span class="text-trwl float-end">
                        @if($status->trainCheckin?->destination_stopover?->isArrivalDelayed)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ $status->trainCheckin->destination_stopover->arrival_planned->format('H:i') }}
                            </small>
                            &nbsp;
                            {{ $status->trainCheckin->destination_stopover->arrival_real->format('H:i') }}
                        @else
                            {{ $status->trainCheckin?->destination_stopover?->arrival?->format('H:i') ?? $status->trainCheckin->arrival->format('H:i') }}
                        @endif
                    </span>
                    {!! stationLink($status->trainCheckin->Destination->name) !!}
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
            <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                @if(auth()?->user()?->id == $status->user_id)
                    {{__('user.you')}}
                @else
                    {{ $status->user->username }}
                @endif
            </a>{{__('dates.-on-')}}
            <a href="{{ url('/status/'.$status->id) }}">
                {{ $status->created_at->format('H:i') }}
            </a>
        </span>
        <ul class="list-inline">
            @auth
                <li class="
                @if(auth()->user()->id == $status->user_id && $status->likes->count() !== 0)d-none @endif list-inline-item d-lg-none"
                    id="avatar-small-{{ $status->id }}" data-trwl-selflike="{{ auth()->user()->id == $status->user_id }}">
                    <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                        <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}"
                             class="profile-image" alt="{{__('settings.picture')}}">
                    </a>
                </li>

                <li class="list-inline-item like-text">
                    <span class="like {{ $status->likes->where('user_id', auth()->user()->id)->first() === null ? 'far fa-star' : 'fas fa-star'}}"
                          data-statusid="{{ $status->id }}"></span>
                    <span class="pl-1 @if($status->likes->count() == 0) d-none @endif"
                          id="like-count-{{ $status->id }}">{{ $status->likes->count() }}</span>
                </li>
                @if(auth()->user()->id == $status->user_id)
                    <li class="list-inline-item like-text">
                        <a href="#" class="edit" data-trwl-status-id="{{ $status->id }}"><i class="fas fa-edit"></i></a>
                    </li>

                    <li class="list-inline-item like-text">
                        <a href="#" class="delete" data-trwl-status-id="{{ $status->id }}"><i class="fas fa-trash"></i></a>
                    </li>
                @endif
            @else
                <li class="list-inline-item d-lg-none" id="avatar-small-{{ $status->id }}">
                    <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                        <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}"
                             class="profile-image" alt="{{__('settings.picture')}}">
                    </a>
                </li>
            @endauth
        </ul>
    </div>

    @if(Route::current()->uri == "status/{id}")
        @foreach($status->likes as $like)
            <div class="card-footer text-muted clearfix">
                <div class="col-xs-2">
                    <a href="{{ route('account.show', ['username' => $like->user->username]) }}">
                        <img src="{{ route('account.showProfilePicture', ['username' => $like->user->username]) }}"
                             class="profile-image float-left" alt="{{__('settings.picture')}}">
                    </a>
                </div>
                <div class="col-xs-10">
                <span class="like-text pl-2 d-table-cell">
                    <a href="{{ route('account.show', ['username' => $like->user->username]) }}">
                        {{$like->user->username}}
                    </a>
                    @if($like->user == $status->user)
                        {{ __('user.liked-own-status') }}
                    @else
                        {{ __('user.liked-status') }}
                    @endif
                </span>
                </div>
            </div>
        @endforeach
    @endif
</div>
