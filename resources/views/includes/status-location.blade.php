<div class="card status mb-3"
     id="status-{{ $status->id }}"
     data-trwl-status-body="{{ $status->body }}"
     data-date="{{$status->locationCheckin->arrival->isoFormat(__('dateformat.with-weekday'))}}"
     data-trwl-business-id="{{ $status->business->value }}"
     data-trwl-visibility="{{ $status->visibility->value }}"
>

    @if (Route::current()->uri === "status/{id}")
        <div class="card-img-top">
            <div id="map-{{ $status->id }}"
                 class="map statusLocationMap embed-responsive embed-responsive-16by9"
            ></div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    let latLng = [{{$status->latitude ?? 0}}, {{$status->longitude ?? 0}}];

                    let map = L.map(document.getElementById('map-{{$status->id}}'), {
                        zoomControl: false,
                        dragging: false,
                        tap: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        touchZoom: false,
                    }).setView(latLng, 8);

                    L.tileLayer(
                        "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
                        {
                            attribution:
                                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
                            subdomains: "abcd",
                            maxZoom: 19
                        }
                    ).addTo(map);

                    const icon = L.divIcon({
                        html: '<i class="fa-solid fa-location-dot" aria-hidden="true" style="line-height: 48px; font-size: 36px;"></i>',
                        iconSize: [48, 48],
                        className: 'text-trwl text-center'
                    });

                    L.marker(latLng, {
                        icon: icon,
                    }).addTo(map);
                });
            </script>
        </div>
    @endif

    <div class="card-body row">
        <div class="col-2 image-box pe-0 d-none d-lg-flex">
            <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                <img src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($status->user) }}"
                     alt="{{ $status->user->username }}"/>
            </a>
        </div>

        <div class="col ps-0">
            <ul style="list-style-type: none; ">
                <li>
                    <a href="{{route('location', ['slug' => $status->locationCheckin->location->slug ?? $status->locationCheckin->location->id])}}"
                       class="text-trwl"
                    >
                        {{$status->locationCheckin->location->name}}
                    </a>
                </li>
                @if(!empty($status->body))
                    <li>
                        <p class="status-body">
                            <i class="fas fa-quote-right" aria-hidden="true"></i>
                            {{ $status->body }}
                        </p>
                    </li>
                @endif
                <li class="train-status text-muted">
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
            data-valuemin="{{ $status->locationCheckin->arrival->timestamp }}"
            data-valuemax="{{ $status->locationCheckin->departure->timestamp }}"
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
                <li class="
                @if(auth()->user()->id == $status->user_id && $status->likes->count() !== 0)d-none @endif list-inline-item d-lg-none"
                    id="avatar-small-{{ $status->id }}"
                    data-trwl-selflike="{{ auth()->user()->id == $status->user_id }}">
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
                    <img
                        src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($like->user) }}"
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
