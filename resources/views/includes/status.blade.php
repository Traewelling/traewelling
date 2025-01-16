@php
    use App\Enum\Business;
    use App\Enum\HafasTravelType;
    use App\Http\Controllers\Backend\Helper\StatusHelper;
    use App\Http\Controllers\Backend\Transport\StationController;
    use App\Http\Controllers\Backend\Transport\StatusController;
    use App\Http\Controllers\Backend\User\ProfilePictureController;use Illuminate\Support\Facades\Gate;
@endphp
@php /** @var App\Models\Status $status */ @endphp
<div class="card status mb-3" id="status-{{ $status->id }}"
     data-trwl-id="{{$status->id}}"
     data-date="{{userTime($status->checkin->departure, __('dateformat.with-weekday'))}}"
     @if(auth()->check() && auth()->id() === $status->user_id)
         data-trwl-status-body="{{ $status->body }}"
     data-trwl-manual-departure="{{ userTime($status->checkin?->manual_departure, 'Y-m-d\TH:i:s', false)}}"
     data-trwl-manual-arrival="{{ userTime($status->checkin?->manual_arrival, 'Y-m-d\TH:i:s', false)}}"
     data-trwl-business-id="{{ $status->business->value }}"
     data-trwl-visibility="{{ $status->visibility->value }}"
     data-trwl-destination-stopover="{{$status->checkin->destinationStopover->id}}"
     data-trwl-alternative-destinations=
         "{{json_encode(StationController::getAlternativeDestinationsForCheckin($status->checkin))}}"
    @endif
>
    @if (Route::current()->uri == "status/{id}")
        <div class="card-img-top">
            <div id="activeJourneys" class="map statusMap embed-responsive embed-responsive-16by9">
                <active-journey-map
                    map-provider="{{ Auth::user()->mapprovider ?? "default" }}"
                    :status-id="{{ $status->id }}"
                    departure="{{ $status->checkin->departure->getTimestamp() }}"
                    arrival="{{ $status->checkin->arrival->getTimestamp() }}"
                >
                </active-journey-map>
            </div>
        </div>
    @endif

    <div class="card-body row">
        <div class="col-2 image-box pe-0 d-none d-lg-flex">
            <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                <img loading="lazy" decoding="async" src="{{ ProfilePictureController::getUrl($status->user) }}"
                     alt="{{ $status->user->username }}">
            </a>
        </div>

        <div class="col ps-0">
            <ul class="timeline">
                <li>
                    <i class="trwl-bulletpoint" aria-hidden="true"></i>
                    <span class="text-trwl float-end">
                        @php
                            $display_departure = $status->checkin->displayDeparture;
                        @endphp
                        @isset($display_departure->original)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ userTime($display_departure->original) }}
                            </small>
                            &nbsp;
                        @endisset
                        <span data-mdb-toggle="tooltip" title="{{$display_departure->type->getTooltip()}}">
                            {{ userTime($display_departure->time) }}
                        </span>
                    </span>

                    <a href="{{route('stationboard', [
                        'stationId' => $status->checkin->originStopover->station->id,
                        'stationName' => $status->checkin->originStopover->station->name,
                    ])}}"
                       class="text-trwl clearfix">
                        @if(auth()->user()?->hasRole('open-beta'))
                            {{$status->checkin->originStopover->station->localized_name}}
                        @else
                            {{$status->checkin->originStopover->station->name}}
                        @endif
                    </a>

                    <p class="train-status text-muted m-0">
                        <span>
                            @if(file_exists(public_path('img/' . $status->checkin->trip->category->value . '.svg')))
                                <img class="product-icon"
                                     src="{{ asset('img/' . $status->checkin->trip->category->value . '.svg') }}"
                                     alt="{{$status->checkin->trip->category->value}}"
                                />
                            @elseif($status->checkin->trip->category === HafasTravelType::PLANE)
                                <i class="fa fa-plane d-inline" aria-hidden="true"></i>
                            @elseif($status->checkin->trip->category === HafasTravelType::TAXI)
                                <i class="fa fa-taxi d-inline" aria-hidden="true"></i>
                            @elseif($status->checkin->trip->category === HafasTravelType::FERRY)
                                <i class="fa fa-ship d-inline" aria-hidden="true"></i>
                            @else
                                <i class="fa fa-train d-inline" aria-hidden="true"></i>
                            @endif
                            {{ $status->checkin->trip->linename }}
                            @if(isset($status->checkin->trip->journey_number) && !str_contains($status->checkin->trip->linename, $status->checkin->trip->journey_number))
                                <small>({{$status->checkin->trip->journey_number}})</small>
                            @endif
                        </span>
                        <span class="ps-2">
                            <i class="fa fa-route d-inline" aria-hidden="true"></i>
                            @if($status->checkin->distance < 1000)
                                {{ $status->checkin->distance }}<small>m</small>
                            @else
                                {{round($status->checkin->distance / 1000)}}<small>km</small>
                            @endif
                        </span>
                        <span class="ps-2">
                            <i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>
                            {!! durationToSpan(secondsToDuration($status->checkin->duration * 60)) !!}
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
                                <a href="{{ route('event', ['slug' => $status->event->slug]) }}">
                                    {{ $status->event->name }}
                                </a>
                            </span>
                        @endif
                    </p>

                    @if(auth()->check() && auth()->id() === $status->user_id)
                        @if($status->moderation_notes)
                            <p class="text-warning font-italic m-0">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                {{ $status->moderation_notes }}
                            </p>
                        @endif

                        @if($status->lock_visibility)
                            <p class="text-warning font-italic m-0">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                {{ __('status.locked-visibility') }}
                            </p>
                        @endif

                        @if($status->hide_body)
                            <p class="text-warning font-italic m-0">
                                <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                {{ __('status.hidden-body') }}
                            </p>
                        @endif
                    @endif

                    @if(!empty($status->body) && Gate::allows('viewBody', $status))
                        <p class="status-body mt-2"><i class="fas fa-quote-right" aria-hidden="true"></i>
                            {!! StatusController::getPrintableEscapedBody($status) !!}
                        </p>
                    @endif

                    @if($status->checkin->departure->isPast() && $status->checkin->arrival->isFuture())
                        <p class="text-muted font-italic">
                            {{ __('stationboard.next-stop') }}

                            @php
                                $nextStation = StatusController::getNextStationForStatus($status);
                            @endphp
                            <a href="{{route('stationboard', [
                                'stationId' => $nextStation?->id,
                                'stationName' => $nextStation?->name
                            ])}}"
                               class="text-trwl clearfix">
                                @if(auth()->user()?->hasRole('open-beta'))
                                    {{$nextStation?->localized_name}}
                                @else
                                    {{$nextStation?->name}}
                                @endif
                            </a>
                        </p>
                    @endif
                </li>
                <li>
                    <i class="trwl-bulletpoint" aria-hidden="true"></i>
                    <span class="text-trwl float-end">
                        @php($display_arrival = $status->checkin->displayArrival)
                        @isset($display_arrival->original)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ userTime($display_arrival->original) }}
                            </small>
                            &nbsp;
                        @endisset
                        <span data-mdb-toggle="tooltip" title="{{$display_arrival->type->getTooltip()}}">
                            {{ userTime($display_arrival->time) }}
                        </span>
                    </span>
                    <a href="{{route('stationboard', [
                        'stationId' => $status->checkin->destinationStopover->station->id,
                        'stationName' => $status->checkin->destinationStopover->station->name
                    ])}}"
                       class="text-trwl clearfix">
                        @if(auth()->user()?->hasRole('open-beta'))
                            {{$status->checkin->destinationStopover->station->localized_name}}
                        @else
                            {{$status->checkin->destinationStopover->station->name}}
                        @endif
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
            data-valuemin="{{ $status->checkin->displayDeparture->time->timestamp }}"
            data-valuemax="{{ $status->checkin->displayArrival->time->timestamp }}"
        ></div>
    </div>
    <div class="card-footer text-muted interaction px-3 px-md-4">
        <ul class="list-inline float-end">
            @can('like', $status)
                <li class="like-text list-inline-item me-0">
                    <a href="{{ auth()->user() ? '#' : route('login') }}"
                       class="like {{ auth()->user() && $status->likes->where('user_id', auth()->user()->id)->first() !== null ? 'fas fa-star' : 'far fa-star'}} {{ $status->user->id === 18574 ? 'peach' : '' }}"
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
                                    data-trwl-share-url="{{ route('status', ['id' => $status->id]) }}"
                                    @if(auth()->user() && $status->user_id == auth()->user()->id)
                                        data-trwl-share-text="{{StatusHelper::getSocialText($status) }}"
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
                                    <a href="{{ route('stationboard', [
                                            'tripId' => $status->checkin->trip->id,
                                            'lineName' => $status->checkin->trip->linename,
                                            'start' => $status->checkin->originStopover->station->id,
                                            'destination' => $status->checkin->destinationStopover->station->id,
                                            'departure' => $status->checkin->originStopover->departure_planned->toIso8601String(),
                                            'idType' => 'trwl'
                                        ]) }}" class="dropdown-item">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                                        </div>
                                        {{__('status.join')}}
                                    </a>
                                </li>
                                <x-mute-button :user="$status->user" :dropdown="true"/>
                                <x-block-button :user="$status->user" :dropdown="true"/>
                                <li>
                                    <a href="{{ route('report', ['subjectType' => 'Status', 'subjectId' => $status->id]) }}"
                                       class="dropdown-item">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-flag" aria-hidden="true"></i>
                                        </div>
                                        {{__('status.report')}}
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
                <a href="{{ route('status', ['id' => $status->id]) }}">
                    {{ userTime($status->created_at) }}
                </a>
            </li>
        </ul>
    </div>
    @if(Gate::allows('like', $status) && Route::current()->uri == "status/{id}")
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
