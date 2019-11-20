<div class="card status mt-3" id="status-{{ $status->id }}" data-body="{{ $status->body }}">

    @if (empty($currentUser))
        <?php $currentUser = Auth::user(); ?>
    @endif

    @if (Route::current()->uri == "status/{id}")
        <?php $mapLines = $status->trainCheckin->getMapLines(); ?>
        @if($mapLines != "[]")
        <div class="card-img-top">
            <div id="map-{{ $status->id }}" class="map statusMap embed-responsive embed-responsive-16by9" data-polygon="{{ $mapLines }}"></div>
        </div>
        @endif
    @endif

    <div class="card-body row">
        <div class="col-2 image-box pr-0 d-none d-lg-flex">
            <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}">
            </a>
        </div>

        <div class="col pl-0">
            <ul class="timeline">
                <li>
                    <i>&nbsp;</i>
                    <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->departure)) }}</span>
                    <span class="text-trwl clearfix">{{ $status->trainCheckin->getOrigin->name }} </span>
                    <p class="train-status text-muted">
                        @php($hafas = $status->trainCheckin->getHafasTrip)
                        <span>
                            @if (file_exists(public_path('img/'.$hafas->category.'.svg')))
                                <img class="product-icon" src="{{ asset('img/'.$hafas->category.'.svg') }}">
                            @else
                                <i class="fa fa-train d-inline"></i>
                            @endif {{ $hafas->linename }}
                        </span>
                        <span>
                            @if($status->business)
                                <i class="pl-2 fas fa-briefcase"></i>
                            @endif
                        </span>
                        <span class="pl-2"><i class="fa fa-route d-inline"></i>&nbsp;{{number($status->trainCheckin->distance, 0)}}<small>km</small></span>
                        @php($dur = secondsToDuration(strtotime($status->trainCheckin->arrival) - strtotime($status->trainCheckin->departure)))
                        <span class="pl-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan($dur) !!}</span>
                    </p>
                    
                    @if(!empty($status->body))
                        <p class="status-body"><i class="fas fa-quote-right"></i> {{ $status->body }}</p>
                    @endif
                </li>
                <li>
                    <i>&nbsp;</i>
                    <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->arrival)) }}</span>
                    <span class="text-trwl">{{ $status->trainCheckin->getDestination->name }}</span>
                </li>
            </ul>
        </div>
    </div>
    <div class="progress">
        <div
            class="progress-bar progress-time"
            role="progressbar"
            style="width: 0%"
            aria-valuenow="{{ time() }}"
            aria-valuemin="{{ strtotime($status->trainCheckin->departure) }}"
            aria-valuemax="{{ strtotime($status->trainCheckin->arrival) }}"
            ></div>
    </div>
    <div class="card-footer text-muted interaction">
        <span class="float-right">
            <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                @if(Auth::check())
                    @if($currentUser->id == $status->user_id)
                        {{__('user.you')}}
                    @else
                        {{ $status->user->username }}
                    @endif
                @else
                    {{ $status->user->username }}
                @endif
            </a>{{__('dates.-on-')}}
            <a href="{{ url('/status/'.$status->id) }}">
                {{ date('H:i', strtotime($status->created_at)) }}
            </a>
        </span>
        <ul class="list-inline">
            @if($currentUser->id == $status->user_id && $status->likes->count() == 0)
            <li class="list-inline-item d-lg-none">
                <a href="{{ route('account.show', ['username' => $status->user->username]) }}">
                    <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}" class="profile-image" alt="{{__('settings.picture')}}">
                </a>
            </li>
            @endif
            @if(Auth::check())
                <li class="list-inline-item">
                    <a href="#" class="like {{ $status->likes->where('user_id', $currentUser->id)->first() === null ? 'far fa-star' : 'fas fa-star'}}" data-statusid="{{ $status->id }}"></a>
                    <span class="pl-1 @if($status->likes->count() == 0) d-none @endif" id="like-count-{{ $status->id }}">{{ $status->likes->count() }}</span>
                </li>
                @if($currentUser->id == $status->user_id)
                    <li class="list-inline-item">
                        <a href="#" class="edit" data-statusid="{{ $status->id }}"><i class="fas fa-edit"></i></a>
                    </li>

                    <li class="list-inline-item">
                        <a href="#" class="delete" data-statusid="{{ $status->id }}"><i class="fas fa-trash"></i></a>
                    </li>
                @endif
            @endif
        </ul>
    </div>

    @if(Route::current()->uri == "status/{id}")
        @foreach($status->likes as $like)
        <div class="card-footer text-muted clearfix">
            <div class="col-xs-2">
                <a href="{{ route('account.show', ['username' => $like->user->username]) }}">
                    <img src="{{ route('account.showProfilePicture', ['username' => $like->user->username]) }}" class="profile-image float-left" alt="{{__('settings.picture')}}">
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
